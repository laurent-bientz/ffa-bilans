<?php

namespace App\Command;

use App\Enum\Gender;
use App\Enum\Trial;
use App\Ffa\GetBreakpoints;
use App\Ffa\GetMetrics;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:cache:warmup')]
class CacheWarmUp extends Command
{
    public function __construct(
        private readonly GetMetrics $getMetrics,
        private GetBreakpoints $getBreakpoints,
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
            ->setDescription('Warmup cache for common metrics/filters.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $time = time();
        $combinations = [];
        $breakpointsGroups = ['year', 'gender'];

        foreach(Trial::cases() as $trial) {
            $combinations[] = ['trial' => $trial->value];
            foreach(Gender::cases() as $gender) {
                $combinations[] = ['trial' => $trial->value, 'gender' => $gender->value];
                $combinations[] = ['trial' => $trial->value, 'breakpoints' => '1', 'gender' => $gender->value];
                $combinations[] = ['trial' => $trial->value, 'breakpoints' => '1', 'gender' => $gender->value, 'group' => $breakpointsGroups[0]];
            }
            $combinations[] = ['trial' => $trial->value, 'breakpoints' => '1'];
            foreach ($breakpointsGroups as $breakpointsGroup) {
                $combinations[] = ['trial' => $trial->value, 'breakpoints' => '1', 'group' => $breakpointsGroup];
            }
        }
        $io->progressStart(\count($combinations));

        foreach ($combinations as $combination) {
            ($this->getMetrics)($combination, null, true);
            ($this->getMetrics)($combination, 'year', true);
            ($this->getBreakpoints)($combination, true);

            $io->progressAdvance();
        }

        $io->success(sprintf('Cache warmed up in %d seconds.', (time() - $time)));

        return Command::SUCCESS;
    }
}
