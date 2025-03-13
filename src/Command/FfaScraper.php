<?php

namespace App\Command;

use App\Ffa\Extractor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:ffa:scrape')]
class FfaScraper extends Command
{
    public function __construct(
        private readonly Extractor $extractor,
    )
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Scrape the results for a given trial/year.')
            ->setDefinition(
                [
                    new InputArgument('id', InputArgument::REQUIRED, 'Trial id'),
                    new InputArgument('year', InputArgument::OPTIONAL, 'Year', date('Y')),
                ]
            )
            ->addUsage(sprintf('%s', 295))
            ->addUsage(sprintf('%s %s', 295, (new \DateTime())->modify('last year')->format('Y')));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $time = time();

        $nbScraped = ($this->extractor)($input->getArgument('id'), $input->getArgument('year'), $io);

        $io->success(sprintf('%d results scraped in %d seconds.', $nbScraped, (time() - $time)));

        return Command::SUCCESS;
    }
}
