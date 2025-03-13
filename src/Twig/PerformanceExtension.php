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

    public function timeFormat($value, bool $displaySeconds = true): ?string
    {
        return $displaySeconds
            ? gmdate("H\hi's", (int)$value)
            : gmdate("H\hi", (int)$value);
    }
}
