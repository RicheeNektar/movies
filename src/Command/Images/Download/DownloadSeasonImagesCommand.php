<?php

namespace App\Command\Images\Download;

use App\Repository\SeasonRepository;
use App\Service\ImageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadSeasonImagesCommand extends Command
{
    protected static $defaultName = 'app:images:download:seasons';
    protected static $defaultDescription = 'Saves all season images as webp.';

    private SeasonRepository $seasonRepository;
    private ImageService $imageService;

    public function __construct(
        SeasonRepository $seasonRepository,
        ImageService $imageService
    ) {
        parent::__construct();
        $this->imageService = $imageService;
        $this->seasonRepository = $seasonRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $basePath = "public/images/season";

        foreach ($this->seasonRepository->findAll() as $season) {
            $filename = "$basePath/{$season->getId()}";

            if (file_exists("$filename.webp")
                && file_exists("$filename.jpeg")
            ) {
                continue;
            }

            $this->imageService->downloadImage($season, $filename);
            $io->writeln("Downloaded cover for '{$season->getSeries()->getTitle()}' S{$season->getSeasonId()}.");
        }

        return Command::SUCCESS;
    }
}