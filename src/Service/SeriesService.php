<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Season;
use App\Entity\Series;
use App\Entity\SeriesBackdrop;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SeriesService
{
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

    public function findEpisodeById(int $seriesId, int $seasonNumber, int $episodeNumber): Episode
    {
        $info = $this->fetchEpisodeInfo($seriesId, $seasonNumber, $episodeNumber);

        $episode = new Episode();
        $episode->setId($episodeNumber);
        $episode->setTitle($info['name']);
        $episode->setAirDate(\DateTimeImmutable::createFromFormat('Y-m-d', $info['air_date']));

        return $episode;
    }

    public function findSeasonById(int $seriesId, int $seasonNumber): Season
    {
        $info = $this->fetchSeasonInfo($seriesId, $seasonNumber);

        $season = new Season();
        $season->setId($seasonNumber);
        $season->setTitle($info['name']);
        $season->setPoster($info['poster_path']);
        $season->setAirDate(\DateTimeImmutable::createFromFormat('Y-m-d', $info['air_date']));

        return $season;
    }

    public function findSeriesById(int $seriesId): Series
    {
        $info = $this->fetchSeriesInfo($seriesId);
        $seriesInfo = $info['series'];

        $series = new Series();
        $series->setId($seriesId);
        $series->setPoster($seriesInfo['poster_path']);
        $series->setTitle($seriesInfo['name']);
        $series->setAirDate(\DateTimeImmutable::createFromFormat('Y-m-d', $seriesInfo['first_air_date']));
        $series->setDescription($seriesInfo['overview']);

        foreach ($info['images']['backdrops'] as $backdropI) {
            $backdrop = new SeriesBackdrop();
            $backdrop->setFile($backdropI['file_path']);

            $series->addBackdrop($backdrop);
        }

        return $series;
    }

    private function fetchEpisodeInfo(int $seriesId, int $seasonNumber, int $episodeNumber)
    {
        return json_decode(
            $this->tmdbClient->request('GET', "tv/$seriesId/season/$seasonNumber/episode/$episodeNumber", [
                'query' => [
                    'language' => 'de-DE'
                ]
            ])->getContent(),
            true
        );
    }

    private function fetchSeasonInfo(int $seriesId, int $seasonNumber)
    {
        return json_decode(
            $this->tmdbClient->request('GET', "tv/$seriesId/season/$seasonNumber", [
                'query' => [
                    'language' => 'de-DE'
                ]
            ])->getContent(),
            true
        );
    }

    private function fetchSeriesInfo(int $tmdbId)
    {
        $seriesInfo = json_decode(
            $this->tmdbClient->request('GET', "tv/$tmdbId", [
                'query' => [
                    'language' => 'de-DE'
                ]
            ])->getContent(),
            true
        );

        $seriesImages = json_decode(
            $this->tmdbClient->request('GET', "tv/$tmdbId/images")->getContent(),
            true
        );

        return [
            'series' => $seriesInfo,
            'images' => $seriesImages,
        ];
    }

    public function updateSeries(Series $series): void
    {
        $info = $this->fetchSeriesInfo($series->getId())['series'];

        $series->setTitle($info['name']);
        $series->setPoster($info['poster_path']);
        $series->setDescription($info['overview']);

        $this->imageService->downloadImage($series, 'series');

        if ($info['first_air_date']) {
            $series->setAirDate(\DateTimeImmutable::createFromFormat('Y-m-d', $info['first_air_date']));
        }
    }

    public function updateSeason(Season $season): void
    {
        $info = $this->fetchSeasonInfo($season->getSeries()->getId(), $season->getSeasonId());

        $season->setTitle($info['name']);
        $season->setPoster($info['poster_path']);

        $this->imageService->downloadImage($season, 'season');

        if ($info['air_date']) {
            $season->setAirDate(\DateTimeImmutable::createFromFormat('Y-m-d', $info['air_date']));
        }
    }

    public function updateEpisode(Episode $episode): void
    {
        $info = $this->fetchEpisodeInfo($episode->getSeries()->getId(), $episode->getSeason()->getSeasonId(), $episode->getEpisodeId());

        $episode->setTitle($info['name']);

        if ($info['air_date']) {
            $episode->setAirDate(\DateTimeImmutable::createFromFormat('Y-m-d', $info['air_date']));
        }
    }

    public function getFolderSize(): int
    {
        return $this->utilService->getFolderSize('../series');
    }
}