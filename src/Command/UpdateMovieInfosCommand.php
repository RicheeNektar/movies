<?php

namespace App\Command;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Service\MovieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand('app:update-movie-infos', 'Updates all movies')]
final class UpdateMovieInfosCommand extends Command
{
    private MovieRepository $movieRepository;
    private EntityManagerInterface $entityManager;
    private MovieService $movieService;

    public function __construct(
        MovieRepository $movieRepository,
        EntityManagerInterface $entityManager,
        MovieService $movieService
    ) {
        parent::__construct();
        $this->movieRepository = $movieRepository;
        $this->entityManager = $entityManager;
        $this->movieService = $movieService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $movies = $this->movieRepository->findAll();

        foreach ($movies as $movie) {
            $this->movieService->updateMovie($movie);
        }

        $this->entityManager->flush();

        $io->success("Updated movies");
        return Command::SUCCESS;
    }
}
