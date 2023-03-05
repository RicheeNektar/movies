<?php

namespace App\Service;

use App\Entity\Movie;
use App\Entity\MovieBackdrop;
use App\Twig\Image;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MovieService {
    private HttpClientInterface $tmdbClient;
    private UtilService $utilService;
    private ImageService $imageService;

    public function __construct(
        HttpClientInterface $tmdbClient,
        UtilService $utilService,
        ImageService $imageService
    ) {
        $this->tmdbClient = $tmdbClient;
        $this->utilService = $utilService;
        $this->imageService = $imageService;
    }

    public function fetchMovieInfo(int $tmdbId): array
    {
        $movieInfo = json_decode(
            $this->tmdbClient->request('GET', "movie/$tmdbId", [
                'query' => [
                    'language' => 'de-DE'
                ]
            ])->getContent(),
            true
        );

        $movieImages = json_decode(
            $this->tmdbClient->request('GET', "movie/$tmdbId/images")->getContent(),
            true
        );

        return [
            'movie' => $movieInfo,
            'images' => $movieImages,
        ];
    }

    public function findById(int $tmdbId): Movie
    {
        $info = $this->fetchMovieInfo($tmdbId);

        $infoMovie = $info['movie'];

        $movie = new Movie();
        $movie->setId($tmdbId);
        $movie->setTitle($infoMovie['title']);
        $movie->setPoster($infoMovie['poster_path']);
        $movie->setAirDate(\DateTimeImmutable::createFromFormat('Y-m-d', $infoMovie['release_date']));
        $movie->setIsHidden(true);
        $movie->setDescription($infoMovie['overview']);

        // Filter backdrops, we do not want any backdrops with translated titles
        $backdrops = $info['images']['backdrops'] ?? [];

        $backdrops = array_filter($backdrops, static function ($backdrop) {
            return $backdrop['iso_639_1'] === null;
        });

        $backdrops = array_map(static function($backdrop) {
            return $backdrop['file_path'];
        }, $backdrops);

        // Persist backdrops in database
        foreach ($backdrops as $backdropPath) {
            $backdrop = new MovieBackdrop();
            $backdrop->setMovie($movie);
            $backdrop->setFile($backdropPath);
            $movie->addBackdrop($backdrop);
        }

        return $movie;
    }

    public function updateMovie(Movie $movie): void
    {
        $info = $this->fetchMovieInfo($movie->getId())['movie'];

        $movie->setTitle($info['title']);
        $movie->setPoster($info['poster_path']);
        $movie->setDescription($info['overview']);

        $this->imageService->downloadImage($movie);

        if ($info['release_date'] !== '') {
            $movie->setAirDate(\DateTimeImmutable::createFromFormat('Y-m-d', $info['release_date']));
        }
    }

    public function getFolderSize(): int
    {
        return $this->utilService->getFolderSize('../movies');
    }
}