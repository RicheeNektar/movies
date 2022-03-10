<?php

namespace App\Command;

use App\Entity\Episode;
use App\Entity\Season;
use App\Entity\Series;
use App\Entity\SeriesBackdrop;
use App\Repository\EpisodeRepository;
use App\Repository\SeasonRepository;
use App\Repository\SeriesRepository;
use App\Service\SeriesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function PHPUnit\Framework\directoryExists;

class UpdateSeriesCommand extends Command
{
    protected static $defaultName = 'app:update-series';

    private EntityManagerInterface $entityManager;
    private SeriesRepository $seriesRepository;
    private SeasonRepository $seasonRepository;
    private EpisodeRepository $episodeRepository;
    private SeriesService $seriesService;

    public function __construct(
        EntityManagerInterface $entityManager,
        SeriesRepository $seriesRepository,
        SeasonRepository $seasonRepository,
        EpisodeRepository $episodeRepository,
        SeriesService $seriesService
    ) {
        parent::__construct();
        $this->seriesRepository = $seriesRepository;
        $this->entityManager = $entityManager;
        $this->seasonRepository = $seasonRepository;
        $this->episodeRepository = $episodeRepository;
        $this->seriesService = $seriesService;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->seriesRepository->findAll() as $series) {
            $tmdbId = $series->getId();
            if (!file_exists("../series/$tmdbId")) {
                $this->entityManager->remove($series);
            }
        }

        foreach (scandir('../series') as $seriesF) {
            if (preg_match('/(\d+)/', $seriesF, $matches)) {
                $seriesId = (int) $matches[0];

                if (!($series = $this->seriesRepository->find($seriesId))) {
                    $series = $this->seriesService->findSeriesById($seriesId);
                }

                foreach (scandir("../series/$seriesId") as $seasonF) {
                    if (preg_match('/(\d+)/', $seasonF, $matches)) {
                        $seasonNumber = (int) $matches[1];

                        if (!($season = $this->seasonRepository->findOneBy([
                                'id' => $seasonNumber,
                                'series' => $series->getId(),
                            ]))
                        ) {
                            $season = $this->seriesService->findSeasonById($seriesId, $seasonNumber);
                            $series->addSeason($season);
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
                                    $episode = $this->seriesService->findEpisodeById($seriesId, $seasonNumber, $episodeNumber);
                                    $episode->setSeries($series);
                                    $season->addEpisode($episode);
                                }
                            }
                        }
                    }
                }
                $this->entityManager->persist($series);
            }
        }

        $this->entityManager->flush();
        return Command::SUCCESS;
    }


}
