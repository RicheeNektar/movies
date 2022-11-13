<?php

namespace App\Service;

use App\Entity\AbstractBackdrop;
use App\Entity\AbstractMedia;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImageService
{
    private string $dir;
    private HttpClientInterface $tmdbClient;

    public function __construct(KernelInterface $kernel, HttpClientInterface $tmdbClient)
    {
        $this->dir = $kernel->getProjectDir();
        $this->tmdbClient = $tmdbClient;
    }

    public function downloadImage(AbstractMedia $media): bool
    {
        try {
            $response = $this->tmdbClient->request('GET', 'https://image.tmdb.org/t/p/w300' . $media->getPoster());
            $image = imagecreatefromstring($response->getContent());
        } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
        }

        if (!isset($image)) {
            return false;
        }

        $file = "$this->dir/public/images/{$media->getAsset()}.webp";

        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        imageantialias($image, true);
        imageresolution($image, 200, 300);
        imagewebp($image, $file);

        return true;
    }

    public function downloadBackdrop(AbstractBackdrop $backdrop): bool
    {
        try {
            $response = $this->tmdbClient->request('GET', 'https://image.tmdb.org/t/p/original' . $backdrop->getFile());
            $image = imagecreatefromstring($response->getContent());
        } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
        }

        if (!isset($image)) {
            return false;
        }

        $file = "$this->dir/public/images/{$backdrop->getAsset()}.webp";

        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        imageantialias($image, true);
        imageresolution($image, 640, 360);
        imagewebp($image, "$this->dir/public/images/{$backdrop->getAsset()}.webp");

        return true;
    }
}