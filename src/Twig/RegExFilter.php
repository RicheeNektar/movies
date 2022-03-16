<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RegExFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('preg_match_all', [$this, 'preg_match_all']),
            new TwigFilter('preg_replace_all', [$this, 'preg_replace_all'])
        ];
    }

    public function preg_replace_all(string $subject, string $pattern, string $replacement): string
    {
        return $this->preg_replace_all($subject, $pattern, $replacement);
    }

    public function preg_match_all(string $subject, string $pattern): array
    {
        preg_match_all($pattern, $subject, $matches);
        return $matches[1];
    }
}