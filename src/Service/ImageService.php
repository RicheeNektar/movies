<?php

namespace App\Service;

use App\Entity\AbstractMedia;
use Symfony\Component\HttpKernel\KernelInterface;

class ImageService
{
    private string $dir;

    public function __construct(KernelInterface $kernel)
    {
        $this->dir = $kernel->getProjectDir();
    }

    public function downloadImage(AbstractMedia $media, string $mediaType): void
    {
        $curl = curl_init('https://image.tmdb.org/t/p/w300' . $media->getPoster());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $image = imagecreatefromstring($response);

        imageantialias($image, true);
        imageresolution($image, 200, 300);
        imagewebp($image, "$this->dir/public/images/$mediaType/{$media->getId()}.webp");
        imagejpeg($image, "$this->dir/public/images/$mediaType/{$media->getId()}.jpeg");
    }
}