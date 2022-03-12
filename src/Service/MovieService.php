<?php

namespace App\Service;

use App\Entity\Movie;
use App\Entity\MovieBackdrop;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MovieService {
    private HttpClientInterface $tmdbClient;

    public function __construct(
        HttpClientInterface $tmdbClient
    ) {
        $this->tmdbClient = $tmdbClient;
    }

    public function fetchMovieInfo(int $tmdbId): array
    {
        $movieInfo = json_decode(
            $this->tmdbClient->request('GET', "movie/$tmdbId", [
                'query' => [
                    'language' => 'de'
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
}