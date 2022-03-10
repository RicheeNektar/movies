<?php

namespace App\Command;

use App\Entity\MovieBackdrop;
use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Repository\RequestRepository;
use App\Service\MovieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateMoviesCommand extends Command
{
    protected static $defaultName = 'app:update-movies';

    private EntityManagerInterface $entityManager;
    private MovieRepository $movieRepository;
    private RequestRepository $requestRepository;
    private MovieService $movieService;

    public function __construct(
        EntityManagerInterface $entityManager,
        MovieRepository $movieRepository,
        RequestRepository $requestRepository,
        MovieService $movieService
    ) {
        parent::__construct();
        $this->movieRepository = $movieRepository;
        $this->entityManager = $entityManager;
        $this->requestRepository = $requestRepository;
        $this->movieService = $movieService;
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->movieRepository->findAll() as $movie) {
            if (!file_exists('../movies/' . $movie->getId() . '.mp4')) {
                if (!$movie->getIsHidden()) {
                    $this->entityManager->remove($movie);
                    echo 'Removed orphaned movie ' . $movie->getId() . ' -> ' . $movie->getTitle() . "\n";
                }
            }
        }

        $this->entityManager->flush();

        try {
            $files = scandir('../movies');

            foreach ($files as $file) {
                if (preg_match('/((\d+)\.mp4)/', $file, $matches)) {
                    $id = (int) $matches[0];
                    $movie = $this->movieRepository->find($id);

                    if (null === $movie) {
                        $movie = $this->movieService->findById($id);
                        $this->entityManager->persist($movie);
                    }

                    if ($movie->getIsHidden()) {
                        $movie->setIsHidden(false);
                        $movie->setCreationDate(new \DateTimeImmutable());

                        echo 'Registered movie ' . $movie->getId() . ' -> ' . $movie->getTitle() . ' ( ' . count($movie->getBackdrops()) . ' Backdrops)' . "\n";

                        $requests = $this->requestRepository->findBy([
                            'movie' => $movie,
                        ]);

                        if ($requests) {
                            foreach ($requests as $request) {
                                $this->entityManager->remove($request);
                            }
                        }
                    }
                }
            }
        } catch(\ErrorException $ex) {
            echo $ex->getMessage().'<br/>';
            foreach($ex->getTrace() as $trace) {
                echo $trace.'<br/>';
            }
        }

        $this->entityManager->flush();
        return Command::SUCCESS;
    }
}
