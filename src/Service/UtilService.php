<?php

namespace App\Service;

class UtilService
{
    public function getFolderSize(string $path): int
    {
        $size = 0;

        foreach ( new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $cur) {
            $size += $cur->getSize();
        }

        return $size;
    }
}