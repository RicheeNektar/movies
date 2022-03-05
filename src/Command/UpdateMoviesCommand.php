<?php

namespace App\Command;

use App\Entity\MovieBackdrop;
use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Repository\RequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateMoviesCommand extends Command
{
    protected static $defaultName = 'app:update-movies';

    private HttpClientInterface $tmdbClient;
    private EntityManagerInterface $entityManager;
    private MovieRepository $movieRepository;
    private RequestRepository $requestRepository;

    public function __construct(
        HttpClientInterface $tmdbClient,
        EntityManagerInterface $entityManager,
        MovieRepository $movieRepository,
        RequestRepository $requestRepository
    ) {
        parent::__construct();
        $this->tmdbClient = $tmdbClient;
        $this->movieRepository = $movieRepository;
        $this->entityManager = $entityManager;
        $this->requestRepository = $requestRepository;
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->movieRepository->findAll() as $movie) {
            if (!file_exists('../movies/' . $movie->getId() . '.mp4')) {
                $this->entityManager->remove($movie);
                echo 'Removed orphaned movie ' . $movie->getId() . ' -> ' . $movie->getTitle() . "\n";
            }
        }

        $files = scandir('../movies');

        foreach ($files as $file) {
            if (preg_match('/((\d+)\.mp4)/', $file, $matches)) {
                $id = (int) $matches[0];

                if (!$this->movieRepository->find($id)) {
                    $info = $this->fetchMovieInfo($id);

                    $infoMovie = $info['movie'];

                    $movie = new Movie();
                    $movie->setId($id);
                    $movie->setTitle($infoMovie['title']);
                    $movie->setPoster($infoMovie['poster_path']);
                    $movie->setYear((int) (substr($infoMovie['release_date'], 0, 4)));
                    $this->entityManager->persist($movie);

                    // Filter backdrops, we do not want any backdrops with translated titles
                    $backdrops = $info['images']['backdrops'] ?? [];

                    $backdrops = array_filter($backdrops, static function ($backdrop) {
                        return $backdrop['iso_639_1'] === null;
                    });

                    $backdrops = array_map(static function($backdrop) {
                        return $backdrop['file_path'];
                    }, $backdrops);

                    // Persist backdrops in database
                    foreach ($backdrops as $backdropPath) {
                        $backdrop = new MovieBackdrop();
                        $backdrop->setMovie($movie);
                        $backdrop->setFile($backdropPath);

                        $this->entityManager->persist($backdrop);
                    }

                    $request = $this->requestRepository->find($id);
                    if ($request) {
                        $this->entityManager->remove($request);
                    }

                    echo 'Registered movie ' . $movie->getId() . ' -> ' . $movie->getTitle() . ' ( ' . count($backdrops) . ' Backdrops)' . "\n";
                }
            }
        }

        $this->entityManager->flush();
        return Command::SUCCESS;
    }

    /**
     * @return array
     */
    private function fetchMovieInfo(int $tmdbId)
    {
        $movieInfo = json_decode(
            $this->tmdbClient->request('GET', "movie/$tmdbId", [
                'query' => [
                    'language' => 'de'
                ]
            ])->getContent(),
            true
        );

        $movieImages = json_decode(
            $this->tmdbClient->request('GET', "movie/$tmdbId/images")->getContent(),
            true
        );

        return [
            'movie' => $movieInfo,
            'images' => $movieImages,
        ];
    }
}
