<?php

namespace App\Command\Images;

use App\Repository\MovieBackdropRepository;
use App\Repository\MovieRepository;
use App\Repository\SeasonRepository;
use App\Repository\SeriesBackdropRepository;
use App\Repository\SeriesRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:images:cleanup', 'Deletes images, that are not used anymore.')]
final class CleanupCommand extends Command
{
    private MovieRepository $movieRepository;
    private SeriesRepository $seriesRepository;
    private SeasonRepository $seasonRepository;
    private SeriesBackdropRepository $seriesBackdropRepository;
    private MovieBackdropRepository $movieBackdropRepository;

    public function __construct(
        MovieRepository $movieRepository,
        SeriesRepository $seriesRepository,
        SeasonRepository $seasonRepository,
        SeriesBackdropRepository $seriesBackdropRepository,
        MovieBackdropRepository $movieBackdropRepository
    )
    {
        parent::__construct();
        $this->movieRepository = $movieRepository;
        $this->seriesRepository = $seriesRepository;
        $this->seasonRepository = $seasonRepository;
        $this->seriesBackdropRepository = $seriesBackdropRepository;
        $this->movieBackdropRepository = $movieBackdropRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ([
                'movie' => $this->movieRepository,
                'series' => $this->seriesRepository,
                'season' => $this->seasonRepository,
                'series/backdrop' => $this->seriesBackdropRepository,
                'movie/backdrop' => $this->movieBackdropRepository,
            ] as $folder => $repo) {

            $deleted = 0;
            $dir = "public/images/$folder";

            $files = scandir($dir);
            if ($files === false) {
                continue;
            }

            foreach ($files as $posterFile) {
                if (preg_match('/(?<id>\d+)\.webp$/i', $posterFile, $matches)) {
                    $id = $matches['id'];
                    if (!$repo->find((int)$id)) {
                        unlink("$dir/$posterFile");
                        $deleted++;
                    }
                }
            }

            $io->info("Deleted $deleted image files from $folder");
        }
        return Command::SUCCESS;
    }
}