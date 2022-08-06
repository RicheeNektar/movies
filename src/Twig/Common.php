<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Common extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('bytes', [$this, "formatBytes"]),
            new TwigFilter('sum', 'array_sum')
        ];
    }

    public function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}