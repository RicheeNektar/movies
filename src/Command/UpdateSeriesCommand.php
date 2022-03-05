<?php

namespace App\Command;

use App\Entity\Episode;
use App\Entity\Season;
use App\Entity\Series;
use App\Entity\SeriesBackdrop;
use App\Repository\EpisodeRepository;
use App\Repository\SeasonRepository;
use App\Repository\SeriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function PHPUnit\Framework\directoryExists;

class UpdateSeriesCommand extends Command
{
    protected static $defaultName = 'app:update-series';

    private HttpClientInterface $tmdbClient;
    private EntityManagerInterface $entityManager;
    private SeriesRepository $seriesRepository;
    private SeasonRepository $seasonRepository;
    private EpisodeRepository $episodeRepository;

    public function __construct(
        HttpClientInterface $tmdbClient,
        EntityManagerInterface $entityManager,
        SeriesRepository $seriesRepository,
        SeasonRepository $seasonRepository,
        EpisodeRepository $episodeRepository
    ) {
        parent::__construct();
        $this->tmdbClient = $tmdbClient;
        $this->seriesRepository = $seriesRepository;
        $this->entityManager = $entityManager;
        $this->seasonRepository = $seasonRepository;
        $this->episodeRepository = $episodeRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->seriesRepository->findAll() as $series) {
            $tmdbId = $series->getTmdbId();
            if (!file_exists("../series/$tmdbId")) {
                $this->entityManager->remove($series);
            }
        }

        foreach (scandir('../series') as $seriesF) {
            if (preg_match('/(\d+)/', $seriesF, $matches)) {
                $seriesId = (int) $matches[0];

                $info = $this->fetchSeriesInfo($seriesId);
                $seriesInfo = $info['series'];

                if (!($series = $this->seriesRepository->find($seriesId))) {
                    $series = new Series();
                    $series->setTmdbId($seriesId);
                    $series->setPoster($seriesInfo['poster_path']);
                    $series->setTitle($seriesInfo['name']);

                    $this->entityManager->persist($series);

                    foreach ($info['images']['backdrops'] as $backdropI) {
                        $backdrop = new SeriesBackdrop();
                        $backdrop->setSeries($series);
                        $backdrop->setFile($backdropI['file_path']);

                        $this->entityManager->persist($backdrop);
                    }
                }

                foreach (scandir("../series/$seriesId") as $seasonF) {
                    if (preg_match('/(\d+)/', $seasonF, $matches)) {
                        $seasonNumber = (int) $matches[1];
                        $seasonInfo = $seriesInfo['seasons'][$seasonNumber];

                        if (!($season = $this->seasonRepository->findOneBy([
                                'id' => $seasonNumber,
                                'series' => $series->getTmdbId(),
                            ]))
                        ) {
                            $season = new Season();
                            $season->setId($seasonNumber);
                            $season->setSeries($series);
                            $season->setName($seasonInfo['name']);
                            $season->setPoster($seasonInfo['poster_path']);
                        }

                        foreach (scandir("../series/$seriesId/$seasonNumber/") as $episodeF) {
                            if (preg_match('/(\d+)\.mp4/', $episodeF, $matches)) {
                                $episodeNumber = (int) $matches[0];

                                if (!$this->episodeRepository->findOneBy([
                                        'id' => $episodeNumber,
                                        'season' => $season->getId(),
                                        'series' => $seriesId,
                                    ])
                                ) {
                                    $episodeInfo = $this->fetchEpisodeInfo($seriesId, $seasonNumber, $episodeNumber);

                                    $episode = new Episode();
                                    $episode->setId($episodeNumber);
                                    $episode->setTitle($episodeInfo['name']);
                                    $episode->setSeries($series);

                                    $season->addEpisode($episode);
                                    $this->entityManager->persist($episode);
                                }
                            }
                        }

                        $series->addSeason($season);
                        $this->entityManager->persist($season);
                    }
                }
            }
        }

        $this->entityManager->flush();
        return Command::SUCCESS;
    }

    private function fetchEpisodeInfo(int $seriesId, int $seasonNumber, int $episodeNumber)
    {
        return json_decode(
            $this->tmdbClient->request('GET', "tv/$seriesId/season/$seasonNumber/episode/$episodeNumber", [
                'query' => [
                    'language' => 'de'
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
                    'language' => 'de'
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
}
