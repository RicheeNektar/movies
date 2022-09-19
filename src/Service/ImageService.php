<?php

namespace App\Service;

use App\Entity\AbstractBackdrop;
use App\Entity\AbstractMedia;
use Symfony\Component\HttpKernel\KernelInterface;

class ImageService
{
    private string $dir;

    public function __construct(KernelInterface $kernel)
    {
        $this->dir = $kernel->getProjectDir();
    }

    public function downloadImage(AbstractMedia $media): bool
    {
        $curl = curl_init('https://image.tmdb.org/t/p/w300' . $media->getPoster());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $image = imagecreatefromstring($response);

        if (!$image) {
            return false;
        }

        imageantialias($image, true);
        imageresolution($image, 200, 300);
        imagewebp($image, "$this->dir/public/images/{$media->getAsset()}.webp");

        return true;
    }

    public function downloadBackdrop(AbstractBackdrop $backdrop): bool
    {
        $curl = curl_init('https://image.tmdb.org/t/p/original' . $backdrop->getFile());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $image = imagecreatefromstring($response);

        if (!$image) {
            return false;
        }

        imageantialias($image, true);
        imageresolution($image, 640, 360);
        imagewebp($image, "$this->dir/public/images/{$backdrop->getAsset()}.webp");

        return true;
    }
}