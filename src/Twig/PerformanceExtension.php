<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PerformanceExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('time_format', $this->timeFormat(...)),
        ];
    }

    public function timeFormat($value): ?string
    {
        return gmdate("H\hi's", (int)$value);
    }
}
