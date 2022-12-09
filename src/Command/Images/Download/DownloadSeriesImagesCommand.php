<?php

namespace App\Command\Images\Download;

use App\Repository\SeriesRepository;
use App\Service\ImageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class DownloadSeriesImagesCommand extends Command
{
    protected static $defaultName = 'app:images:download:series';
    protected static $defaultDescription = 'Downloads all series images.';

    private SeriesRepository $seriesRepository;
    private ImageService $imageService;
    private KernelInterface $kernel;

    public function __construct(
        KernelInterface $kernel,
        SeriesRepository $seriesRepository,
        ImageService $imageService
    ) {
        parent::__construct();
        $this->kernel = $kernel;
        $this->imageService = $imageService;
        $this->seriesRepository = $seriesRepository;
    }

    protected function configure()
    {
        $this->addOption('all', 'a', InputOption::VALUE_NONE, 'With this, it will also download season images.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $all = $input->getOption('all');
        $io = new SymfonyStyle($input, $output);

        $basePath = "{$this->kernel->getProjectDir()}/public/images/season";

        foreach ($this->seriesRepository->findAll() as $series) {
            $filename = "$basePath/{$series->getId()}";

            if (file_exists("$filename.webp")
            ) {
                continue;
            }

            if ($this->imageService->downloadImage($series)) {
                $io->writeln("Downloaded poster for '{$series->getTitle()}'.");
            } else {
                $io->error("Download failed for '{$series->getTitle()}'.");
            }
        }

        if ($all) {
           $app = $this->getApplication();
           $app->setAutoExit(false);

           $io->info('Downloading season images...');

           try {
               $in = new ArrayInput([
                   'command' => 'app:images:download:seasons',
               ]);
               $out = new BufferedOutput();
               $app->run($in, $out);
               $io->text($out->fetch());
           } catch(\ErrorException $e) {
               $io->error($e->getMessage());
           }
        }

        return Command::SUCCESS;
    }
}