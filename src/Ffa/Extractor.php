<?php

namespace App\Ffa;

use App\Entity\Performance;
use App\Enum\Gender;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\Mapping as ORM;

class Extractor
{
    private string $url = 'https://bases.athle.fr/asp.net/liste.aspx?frmpostback=true&frmbase=bilans&frmmode=1&frmespace=0&frmepreuve=%d&frmannee=%d&frmsexe=%s&frmposition=%d';

    private int $nbScraped = 0;

    private int $nbResultsPerPage = 250;

    private int $minimumAge = 10;

    private array $performances = [];

    private array $performanceProperties = [];

    public function __construct(
        private readonly HttpClientInterface    $client,
        private readonly ValidatorInterface     $validator,
        private readonly EntityManagerInterface $em,
    )
    {
        $this->guessPerformanceProperties();
    }

    public function __invoke(
        int $trialId,
        int $year,
        ?SymfonyStyle $io = null,
    ): int
    {
        // scrape both genders
        foreach(['M', 'F'] as $gender) {
            $io?->info(sprintf('Scrapping %s performances', 'F' === $gender ? 'women' : 'men'));
            $this->scrape($trialId, $year, $gender, 0, $io);
            $io?->progressFinish();
        }

        return $this->nbScraped;
    }

    private function scrape(
        int $trialId,
        int $year,
        string $gender = 'M',
        int $page = 0,
        ?SymfonyStyle $io = null,
    ): void
    {
        try {
            $response = $this->client->request('GET', sprintf($this->url, $trialId, $year, $gender, $page));
            $crawler = new Crawler($response->getContent());
            $nbTotalResults = !empty($totalResults = $crawler->filterXPath('//td[@class="barCount"]')->text()) ? (int)(new UnicodeString($totalResults))->trimEnd(" enr.")->toString() : 0;

            if (0 === $page) {
                $io?->progressStart($nbTotalResults);
            }

            $results = $crawler->filterXPath('//table[@id="ctnBilans"]/tr[position() > 2]')->each(function (Crawler $node) use ($trialId, $year, $gender, $io) {
                $formattedTime = $node->filterXPath('//td[3]')->filterXPath('//b')->text();
                $formattedDate = $node->filterXPath('//td[19]')->text();
                $formattedBirth = $node->filterXPath('//td[17]')->text();

                try {
                    $properTime = new \DateTime(date('Y-m-d') . str_replace(['h', '\''], ':', (new UnicodeString($formattedTime))->trimEnd("''")));
                    $timeDiff = $properTime->diff(new \DateTime("today"));
                    $properDate = new \DateTime(implode('-', array_reverse(explode('/', $formattedDate))));

                    // prepare performance
                    $performance = (new Performance())
                        ->setDate($properDate)
                        ->setTime(($timeDiff->h * 3600) + ($timeDiff->i * 60) + $timeDiff->s)
                        ->setTimeFormatted(sprintf("%02dh%02d'%02d", $timeDiff->h, $timeDiff->i, $timeDiff->s))
                        ->setLocation($node->filterXPath('//td[21]')->text())
                        ->setGender('F' === $gender ? Gender::WOMAN : Gender::MAN)
                        ->setName($node->filterXPath('//td[7]')->text())
                        ->setBirth(!empty($formattedBirth) ? ($formattedBirth > (date('y') - $this->minimumAge) ? '19' . $formattedBirth : '20' . $formattedBirth) : null)
                        ->setCategory(!empty($category = $node->filterXPath('//td[15]')->text()) ? $category : null)
                        ->setClub(!empty($club = $node->filterXPath('//td[9]')->text()) && 'nl la veille de la compétition' !== strtolower($club) ? (new UnicodeString($club))->trimEnd(" *") : null)
                        ->setLeague(!empty($league = $node->filterXPath('//td[11]')->text()) ? $league : null)
                        ->setZip(!empty($zip = $node->filterXPath('//td[13]')->text()) ? $zip : null)
                        ->setTrial($trialId)
                        ->setYear($year);

                    // validate it
                    $this->validatePerformance($performance);

                    // garbage collector
                    $this->em->detach($performance);
                    unset($performance);

                    $io?->progressAdvance();

                    // insert/update performances by chunks of 50
                    if (0 < $this->nbScraped && 0 === $this->nbScraped % 50) {
                        $this->batchInsert();
                    }
                }
                catch (\Exception $e) {
                    # TODO: log date / xPath error
                }
            });

            // insert/update remaining performances
            $this->batchInsert();

            if (($page + 1) * $this->nbResultsPerPage < $nbTotalResults) {
                $this->scrape($trialId, $year, $gender, $page + 1, $io);
            }
        }
        catch (\Exception $e) {
            # TODO: log api error
        }
    }

    private function validatePerformance(Performance $performance): void
    {
        // compose the final uid
        $performance->setUid(hash('sha256', $performance->getTrial() . '-' . $performance->getYear() . '-' . $performance->getName() . '-' . $performance->getBirth() . '-' . $performance->getTime()));

        if (0 === ($violations = $this->validator->validate($performance))->count()) {
            $data = [];
            foreach($this->performanceProperties as $performanceProperty) {
                $performanceValue = PropertyAccess::createPropertyAccessorBuilder()
                    ->disableExceptionOnInvalidPropertyPath()
                    ->disableExceptionOnInvalidIndex()
                    ->getPropertyAccessor()
                    ->getValue($performance, $performanceProperty);

                // format datetime
                if ($performanceValue instanceof \DateTime) {
                    $performanceValue = $performanceValue->format('Y-m-d H:i:s');
                }
                // enum
                elseif ($performanceValue instanceof \BackedEnum) {
                    $performanceValue = $performanceValue->value;
                }
                // format foreign key instead of object
                elseif (is_object($performanceValue) && method_exists($performanceValue, $method = 'getId')) {
                    $performanceProperty .= 'Id';
                    $performanceValue = $performanceValue->$method();
                }
                // camelCase to `snake_case`
                $data['`' . InflectorFactory::create()->build()->tableize($performanceProperty) . '`'] = (null !== $performanceValue) ? trim($performanceValue) : null;

                $this->nbScraped++;
            }

            $this->performances[] = $data;
        }
        else {
            # TODO: log validation error
        }
    }

    public function batchInsert(): void
    {
        if (empty($this->performances)) {
            return;
        }

        foreach($this->performances as $performance) {
            $columnsToInsert = array_keys($performance);
            $valuesToInsert = array_values($performance);
            $columnsToUpdate = array_diff($columnsToInsert, ['`uid`']);

            $sql = "INSERT INTO `performance` (";
            $sql .= implode(", ", $columnsToInsert);
            $sql .= ") VALUES (";
            $sql .= implode(", ", array_map(fn ($item) => null === $item || "" === trim($item) ? "NULL" : (is_string($item) ? "'" . addslashes($item) . "'" : $item), $valuesToInsert));
            $sql .= ") ON DUPLICATE KEY UPDATE ";
            $sql .= implode(", ", array_map(fn ($item) => $item . ' = VALUES(' . $item . ')', $columnsToUpdate));
            $sql .= ";";

            try {
                $this->em->getConnection()->prepare($sql)->executeStatement();
                $this->em->clear();
            }
            catch (\Exception $e) {
                # TODO: log sql error

                continue;
            }
        }

        $this->performances = [];
    }

    private function guessPerformanceProperties(): void
    {
        $pudoMetadata = $this->em->getClassMetadata(Performance::class);
        foreach(array_merge($pudoMetadata->fieldMappings ?? [], $pudoMetadata->associationMappings ?? []) as $property => $data)
        {
            // exclude primary key
            if (null === ($pudoMetadata->getReflectionProperty($property)->getAttributes(ORM\Id::class)[0] ?? null)) {
                $this->performanceProperties[] = $property;
            }
        }
    }
}