<?php

namespace App\Command\Images\Download;

use App\Repository\SeasonRepository;
use App\Service\ImageService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand('app:images:download:seasons', 'Downloads all season images.')]
final class DownloadSeasonImagesCommand extends Command
{
    private SeasonRepository $seasonRepository;
    private ImageService $imageService;
    private KernelInterface $kernel;

    public function __construct(
        KernelInterface $kernel,
        SeasonRepository $seasonRepository,
        ImageService $imageService
    ) {
        parent::__construct();
        $this->kernel = $kernel;
        $this->imageService = $imageService;
        $this->seasonRepository = $seasonRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $basePath = "{$this->kernel->getProjectDir()}/public/images/season";

        foreach ($this->seasonRepository->findAll() as $season) {
            $filename = "$basePath/{$season->getId()}";

            if (file_exists("$filename.webp")
            ) {
                continue;
            }

            if ($this->imageService->downloadImage($season)) {
                $io->writeln("Downloaded poster for '{$season->getSeries()->getTitle()}' S{$season->getSeasonId()}.");
            } else {
                $io->error("Download failed for '{$season->getSeries()->getTitle()}' S{$season->getSeasonId()}.");
            }
        }

        return Command::SUCCESS;
    }
}