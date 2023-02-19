<?php

namespace App\Command\Images\Download;

use App\Repository\SeriesBackdropRepository;
use App\Service\ImageService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand('app:images:download:series-backdrops', 'Downloads all series backdrops.')]
final class DownloadSeriesBackdrops extends Command
{
    private SeriesBackdropRepository $seriesBackdropRepository;
    private ImageService $imageService;
    private KernelInterface $kernel;

    public function __construct(
        KernelInterface $kernel,
        SeriesBackdropRepository $seriesBackdropRepository,
        ImageService $imageService
    ) {
        parent::__construct();
        $this->kernel = $kernel;
        $this->imageService = $imageService;
        $this->seriesBackdropRepository = $seriesBackdropRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $basePath = "{$this->kernel->getProjectDir()}/public/images/series/backdrop";

        foreach ($this->seriesBackdropRepository->findAll() as $backdrop) {
            $filename = "$basePath/{$backdrop->getId()}";

            if (file_exists("$filename.webp")
            ) {
                continue;
            }

            if ($this->imageService->downloadBackdrop($backdrop)) {
                $io->writeln("Downloaded backdrop for '{$backdrop->getSeries()->getTitle()}'");
            } else {
                $io->error("Download failed for '{$backdrop->getSeries()->getTitle()}'.");
            }
        }

        return Command::SUCCESS;
    }
}