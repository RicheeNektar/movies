<?php

namespace App\Command;

use App\Entity\Message;
use App\Repository\MovieRepository;
use App\Repository\RequestRepository;
use App\Service\MovieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand('app:update-movies')]
final class UpdateMoviesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private MovieRepository $movieRepository;
    private RequestRepository $requestRepository;
    private MovieService $movieService;
    private TranslatorInterface $translator;
    private KernelInterface $kernel;

    public function __construct(
        KernelInterface $kernel,
        EntityManagerInterface $entityManager,
        MovieRepository $movieRepository,
        RequestRepository $requestRepository,
        MovieService $movieService,
        TranslatorInterface $translator
    ) {
        parent::__construct();
        $this->kernel = $kernel;
        $this->movieRepository = $movieRepository;
        $this->entityManager = $entityManager;
        $this->requestRepository = $requestRepository;
        $this->movieService = $movieService;
        $this->translator = $translator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $moviesDir = "{$this->kernel->getProjectDir()}/movies";

        foreach ($this->movieRepository->findAll() as $movie) {
            if (!file_exists("$moviesDir/{$movie->getId()}.mp4")) {
                if (!$movie->getIsHidden()) {
                    $this->entityManager->remove($movie);
                    $io->writeln("Removed orphaned movie '{$movie->getTitle()}'.");
                }
            }
        }

        $this->entityManager->flush();

        $files = scandir($moviesDir);

        foreach ($files as $file) {
            if (preg_match('/^((\d+)\.mp4)$/', $file, $matches)) {
                $id = (int) $matches[0];
                $movie = $this->movieRepository->find($id);

                if (null === $movie) {
                    $movie = $this->movieService->findById($id);
                    $this->entityManager->persist($movie);
                }

                if ($movie->getIsHidden()) {
                    $movie->setIsHidden(false);
                    $movie->setCreationDate(new \DateTimeImmutable());

                    $io->writeln("Registered movie '{$movie->getTitle()}' (" . count($movie->getBackdrops()) . ").");

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

        $this->entityManager->flush();
        return Command::SUCCESS;
    }
}
