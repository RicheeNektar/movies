<?php

namespace App\Command;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Repository\SeriesRepository;
use App\Service\MovieService;
use App\Service\SeriesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateSeriesInfosCommand extends Command
{
    protected static $defaultName = 'app:update-series-infos';
    protected static $defaultDescription = 'Updates all series';

    private SeriesRepository $seriesRepository;
    private EntityManagerInterface $entityManager;
    private SeriesService $seriesService;

    public function __construct(
        SeriesRepository $seriesRepository,
        EntityManagerInterface $entityManager,
        SeriesService $seriesService
    ) {
        parent::__construct();
        $this->seriesRepository = $seriesRepository;
        $this->entityManager = $entityManager;
        $this->seriesService = $seriesService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->seriesRepository->findAll() as $series) {
            $this->seriesService->updateSeries($series);

            foreach ($series->getSeasons() as $season) {
                $this->seriesService->updateSeason($season);

                foreach ($season->getEpisodes() as $episode) {
                    $this->seriesService->updateEpisode($episode);
                }
            }
        }

        $this->entityManager->flush();

        $io->success("Updated series");
        return Command::SUCCESS;
    }
}
