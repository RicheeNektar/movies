<?php

namespace App\Command\Images\Download;

use App\Repository\MovieBackdropRepository;
use App\Service\ImageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class DownloadMovieBackdrops extends Command
{
    protected static $defaultName = 'app:images:download:movie-backdrops';
    protected static $defaultDescription = 'Downloads all movie backdrops.';

    private MovieBackdropRepository $movieBackdropRepository;
    private ImageService $imageService;
    private KernelInterface $kernel;

    public function __construct(
        KernelInterface $kernel,
        MovieBackdropRepository $movieBackdropRepository,
        ImageService $imageService
    ) {
        parent::__construct();
        $this->kernel = $kernel;
        $this->imageService = $imageService;
        $this->movieBackdropRepository = $movieBackdropRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $basePath = "{$this->kernel->getProjectDir()}/public/images/movie/backdrop";

        $groups = [];

        foreach ($this->movieBackdropRepository->findAll() as $backdrop) {
            if(!isset($groups[$backdrop->getMovie()->getTitle()])) {
                $groups[$backdrop->getMovie()->getTitle()] = [];
            }
            $groups[$backdrop->getMovie()->getTitle()][] = $backdrop;
        }

        foreach ($groups as $movie => $backdrops) {
            $io->writeln("Downloading backdrops for '$movie'");
            $io->progressStart(count($backdrops));

            foreach($backdrops as $backdrop) {
                $filename = "$basePath/{$backdrop->getId()}";

                if (file_exists("$filename.webp")
                ) {
                    continue;
                }

                if ($this->imageService->downloadBackdrop($backdrop)) {
                    $io->writeln("Downloaded backdrop for '{$backdrop->getMovie()->getTitle()}'");
                } else {
                    $io->error("Download failed for '{$backdrop->getMovie()->getTitle()}'.");
                }
                $io->progressAdvance();
            }

            $io->progressFinish();
        }

        return Command::SUCCESS;
    }
}