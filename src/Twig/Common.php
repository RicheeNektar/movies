<?php

namespace App\Twig;

use App\Entity\AbstractMedia;
use App\Entity\Episode;
use App\Entity\Season;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Common extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('bytes', [$this, "formatBytes"]),
            new TwigFilter('sum', 'array_sum')
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('isSeason', [$this, "isSeason"]),
        ];
    }

    public function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function isSeason(AbstractMedia $media): bool
    {
        return $media instanceof Season;
    }
}