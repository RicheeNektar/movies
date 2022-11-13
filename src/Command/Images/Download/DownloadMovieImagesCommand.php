<?php

namespace App\Command\Images\Download;

use App\Repository\MovieRepository;
use App\Service\ImageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class DownloadMovieImagesCommand extends Command
{
    protected static $defaultName = 'app:images:download:movies';
    protected static $defaultDescription = 'Downloads all movie images.';

    private MovieRepository $movieRepository;
    private ImageService $imageService;
    private KernelInterface $kernel;

    public function __construct(
        KernelInterface $kernel,
        MovieRepository $movieRepository,
        ImageService $imageService
    ) {
        parent::__construct();
        $this->kernel = $kernel;
        $this->imageService = $imageService;
        $this->movieRepository = $movieRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $basePath = "{$this->kernel->getProjectDir()}/public/images/movie";

        foreach ($this->movieRepository->findAll() as $movie) {
            $filename = "$basePath/{$movie->getId()}";

            if (file_exists("$filename.webp")
            ) {
                continue;
            }

            if ($this->imageService->downloadImage($movie)) {
                $io->writeln("Downloaded poster for '{$movie->getTitle()}'.");
            } else {
                $io->error("Download failed for '{$movie->getTitle()}'.");
            }
        }

        return Command::SUCCESS;
    }
}