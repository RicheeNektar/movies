<?php

namespace App\Command;

use App\Repository\EpisodeRepository;
use App\Repository\SeasonRepository;
use App\Repository\SeriesRepository;
use App\Service\SeriesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand('app:update-series')]
final class UpdateSeriesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private SeriesRepository $seriesRepository;
    private SeasonRepository $seasonRepository;
    private EpisodeRepository $episodeRepository;
    private SeriesService $seriesService;
    private KernelInterface $kernel;

    public function __construct(
        KernelInterface $kernel,
        EntityManagerInterface $entityManager,
        SeriesRepository $seriesRepository,
        SeasonRepository $seasonRepository,
        EpisodeRepository $episodeRepository,
        SeriesService $seriesService
    ) {
        parent::__construct();
        $this->kernel = $kernel;
        $this->seriesRepository = $seriesRepository;
        $this->entityManager = $entityManager;
        $this->seasonRepository = $seasonRepository;
        $this->episodeRepository = $episodeRepository;
        $this->seriesService = $seriesService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $seriesDir = "{$this->kernel->getProjectDir()}/series";

        foreach ($this->episodeRepository->findAll() as $episode) {
            $seriesId = $episode->getSeries()->getId();
            $seasonId = $episode->getSeason()->getSeasonId();
            $episodeId = $episode->getEpisodeId();
            if (!file_exists("$seriesDir/$seriesId/$seasonId/$episodeId.mp4")) {
                $this->entityManager->remove($episode);
            }
        }

        foreach ($this->seasonRepository->findAll() as $season) {
            $seriesId = $season->getSeries()->getId();
            $seasonId = $season->getSeasonId();
            if (!file_exists("$seriesDir/$seriesId/$seasonId")) {
                foreach($season->getEpisodes() as $episode) {
                    $this->entityManager->remove($episode);
                }
                $this->entityManager->remove($season);
            }
        }

        foreach ($this->seriesRepository->findAll() as $series) {
            $seriesId = $series->getId();
            if (!file_exists("$seriesDir/$seriesId")) {
                foreach($series->getSeasons() as $season) {
                    foreach ($season->getEpisodes() as $episode) {
                        $this->entityManager->remove($episode);
                    }
                    $this->entityManager->remove($season);
                }
                $this->entityManager->remove($series);
            }
        }

        $this->entityManager->flush();

        foreach (scandir($seriesDir) as $seriesF) {
            if (preg_match('/^(\d+)$/', $seriesF, $matches)) {
                $seriesId = (int)$matches[0];

                if (!($series = $this->seriesRepository->find($seriesId))) {
                    $series = $this->seriesService->findSeriesById($seriesId);
                }

                $seasonDir = "$seriesDir/$seriesId";
                foreach (scandir($seasonDir) as $seasonF) {
                    if (preg_match('/^(\d+)$/', $seasonF, $matches)) {
                        $seasonNumber = (int)$matches[1];

                        if (!($season = $this->seasonRepository->findOneBy([
                            'seasonId' => $seasonNumber,
                            'series' => $series,
                        ]))
                        ) {
                            $season = $this->seriesService->findSeasonById($seriesId, $seasonNumber);
                            $season->setSeries($series);
                            $season->setSeasonId($seasonNumber);
                            $series->addSeason($season);
                        }

                        foreach (scandir("$seasonDir/$seasonNumber") as $episodeF) {
                            if (preg_match('/^(\d+)\.mp4$/', $episodeF, $matches)) {
                                $episodeNumber = (int)$matches[0];

                                if (!$this->episodeRepository->findOneBy([
                                    'episodeId' => $episodeNumber,
                                    'season' => $season,
                                    'series' => $series,
                                ])
                                ) {
                                    $episode = $this->seriesService->findEpisodeById($seriesId, $seasonNumber, $episodeNumber);
                                    $episode->setEpisodeId($episodeNumber);
                                    $episode->setSeries($series);
                                    $episode->setSeason($season);
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
