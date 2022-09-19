<?php

namespace App\Command\Images\Download;

use App\Repository\MovieRepository;
use App\Service\ImageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadMovieImagesCommand extends Command
{
    protected static $defaultName = 'app:images:download:movies';
    protected static $defaultDescription = 'Saves all movie images as webp.';

    private MovieRepository $movieRepository;
    private ImageService $imageService;

    public function __construct(
        MovieRepository $movieRepository,
        ImageService $imageService
    ) {
        parent::__construct();
        $this->imageService = $imageService;
        $this->movieRepository = $movieRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $basePath = "public/images/movie";

        foreach ($this->movieRepository->findAll() as $movie) {
            $filename = "$basePath/{$movie->getId()}";

            if (file_exists("$filename.webp")
                && file_exists("$filename.jpeg")
            ) {
                continue;
            }

            $this->imageService->downloadImage($movie, $filename);
            $io->writeln("Downloaded cover for '{$movie->getTitle()}'.");
        }

        return Command::SUCCESS;
    }
}