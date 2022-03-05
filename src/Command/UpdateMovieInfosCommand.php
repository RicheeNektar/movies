<?php

namespace App\Command;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateMovieInfosCommand extends Command
{
    protected static $defaultName = 'app:update-movie-infos';
    protected static $defaultDescription = 'Updates all movies';

    private MovieRepository $movieRepository;
    private HttpClientInterface $tmdbClient;
    private EntityManagerInterface $entityManager;

    public function __construct(
        MovieRepository $movieRepository,
        HttpClientInterface $tmdbClient,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->movieRepository = $movieRepository;
        $this->tmdbClient = $tmdbClient;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $movies = $this->movieRepository->findAll();
        $count = count($movies);

        $updated = 0;

        foreach ($movies as $movie) {
            $tmdbId = $movie->getId();
            $response = $this->tmdbClient->request('GET', "movie/$tmdbId", [
                'query' => [
                    'language' => 'de-DE',
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getContent(), true);

                $movie->setTitle($data['title']);
                $movie->setPoster($data['poster_path']);
                $movie->setYear((int) (substr($data['release_date'], 0, 4)));

                $this->entityManager->persist($movie);
                $updated++;
            } else {
                $io->info("Failed to fetch info for $tmdbId");
            }
        }

        $this->entityManager->flush();

        $io->success("Updated $updated / $count movies");
        return Command::SUCCESS;
    }
}
