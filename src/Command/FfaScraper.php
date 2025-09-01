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
                    new InputArgument('id', InputArgument::REQUIRED, 'Trial id', null, [261, 271, 295, 299]),
                    new InputArgument('year', InputArgument::OPTIONAL, 'Year', date('Y')),
                    new InputArgument('gender', InputArgument::OPTIONAL, 'Gender', null, ['M', 'F']),
                    new InputArgument('page', InputArgument::OPTIONAL, 'Page', 0),
                ]
            )
            ->addUsage(sprintf('%d', 295))
            ->addUsage(sprintf('%d %d', 295, (new \DateTime())->modify('last year')->format('Y')))
            ->addUsage(sprintf('%d %d %s', 295, (new \DateTime())->modify('2 years ago')->format('Y'), 'F'));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $time = time();

        $nbScraped = ($this->extractor)($input->getArgument('id'), $input->getArgument('year'), $input->getArgument('gender'), $input->getArgument('page'), $io);

        $io->success(sprintf('%d results scraped in %d seconds.', $nbScraped, (time() - $time)));

        return Command::SUCCESS;
    }
}
