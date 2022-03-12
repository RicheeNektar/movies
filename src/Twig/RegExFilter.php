<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RegExFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('preg_match_all', [$this, 'preg_match_all'])
        ];
    }

    public function preg_match_all(string $subject, string $pattern): array
    {
        preg_match_all($pattern, $subject, $matches);
        return $matches[1];
    }
}