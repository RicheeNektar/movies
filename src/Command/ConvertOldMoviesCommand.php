<?php

namespace App\Command;

use App\Repository\MoviesRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConvertOldMoviesCommand extends Command
{
    protected static $defaultName = 'app:convert-old-movies';
    protected static $defaultDescription = 'Converts old movie structure to newer one';

    private MoviesRepository $moviesRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        MoviesRepository $moviesRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->moviesRepository = $moviesRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->addOption('delete');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $oldMovies = $this->moviesRepository->findAll();

        $withDelete = $input->getOption('delete');

        foreach ($oldMovies as $movie) {
            $file = $movie->getFile();
            $tmdbId = $movie->getId();

            $f = "../movies/$file";
            if (file_exists($f)) {
                rename($f, "../movies/$tmdbId.mp4");
                $this->entityManager->remove($movie);

                if ($withDelete) {
                    $this->entityManager->remove($movie);
                }

                $io->info("Renamed $tmdbId ($file)");
            }
        }

        $this->entityManager->flush();

        $command = $this->getApplication()->find('app:update-movies');
        return $command->run(new ArrayInput([]), $output);
    }
}
