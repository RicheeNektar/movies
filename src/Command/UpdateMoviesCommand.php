<?php

namespace App\Command;

use App\Entity\Message;
use App\Repository\MovieRepository;
use App\Repository\RequestRepository;
use App\Service\MovieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UpdateMoviesCommand extends Command
{
    protected static $defaultName = 'app:update-movies';

    private EntityManagerInterface $entityManager;
    private MovieRepository $movieRepository;
    private RequestRepository $requestRepository;
    private MovieService $movieService;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        MovieRepository $movieRepository,
        RequestRepository $requestRepository,
        MovieService $movieService,
        TranslatorInterface $translator
    ) {
        parent::__construct();
        $this->movieRepository = $movieRepository;
        $this->entityManager = $entityManager;
        $this->requestRepository = $requestRepository;
        $this->movieService = $movieService;
        $this->translator = $translator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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
                                $message = new Message();
                                $message
                                    ->setUser($request->getUser())
                                    ->setTitle($this->translator->trans('messages.movie_added.title'))
                                    ->setText(
                                        $this->translator->trans(
                                            'messages.movie_added.text',
                                            [
                                                'title' => $request->getTitle(),
                                            ]
                                        )
                                    )
                                ;

                                $this->entityManager->persist($message);
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
