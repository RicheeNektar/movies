<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Movie;
use App\Entity\Season;
use App\Entity\Series;
use App\Repository\EpisodeRepository;
use App\Repository\MovieBackdropRepository;
use App\Repository\SeriesBackdropRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PlayerController extends AbstractController
{
    private MovieBackdropRepository $movieBackdropRepository;
    private SeriesBackdropRepository $seriesBackdropRepository;
    private EpisodeRepository $episodeRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        MovieBackdropRepository $movieBackdropRepository,
        SeriesBackdropRepository $seriesBackdropRepository,
        EpisodeRepository $episodeRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->movieBackdropRepository = $movieBackdropRepository;
        $this->seriesBackdropRepository = $seriesBackdropRepository;
        $this->episodeRepository = $episodeRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/movie/{movie<\d+>}", name="movie-player")
     */
    public function moviePlayer(UserInterface $iUser, Request $request, Movie $movie): Response
    {
        if ($request->query->getBoolean('watched', false) === true) {
            $user = $this->userRepository->findOneBy(['username' => $iUser->getUserIdentifier()]);
            $user->addWatchedMovie($movie);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json(['success' => 'true']);
        }

        $backdrop = $this->movieBackdropRepository->findRandomBackdropFor($movie);

        return $this->render('player/movies.html.twig', [
            'movie' => $movie,
            'backdrop' => $backdrop,
        ]);
    }

    /**
     * @Route("/tv/{series<\d+>}/{season<\d+>}/{episode<\d+>}", name="tv-player")
     */
    public function tvPlayer(Series $series, Season $season, Episode $episode): Response
    {
        $backdrop = $this->seriesBackdropRepository->findRandomBackdropFor($series);

        $episode_count = $this->episodeRepository->count([
            'series' => $series->getTmdbId(),
            'season' => $season->getId(),
        ]);

        return $this->render('player/series.html.twig', [
            'series' => $series,
            'season' => $season,
            'episode' => $episode,
            'episode_count' => $episode_count,
            'backdrop' => $backdrop,
        ]);
    }

    /**
     * @Route("/movie/{movie<\d+>}/file", name="movie-file")
     */
    public function movieFile(Movie $movie): Response
    {
        return $this
            ->file("../movies/" . $movie->getId() . ".mp4")
            ->setCache([
                'no_cache' => true,
                'no_store' => true,
                'must_revalidate' => true,
            ])
        ;
    }

    /**
     * @Route("/tv/{series<\d+>}/{season<\d+>}/{episode<\d+>}/file", name="tv-file")
     */
    public function tvFile(Series $series, Season $season, Episode $episode): Response
    {
        $tvId = $series->getTmdbId();
        $seasonId = $season->getId();
        $episodeId = $episode->getId();

        return $this
            ->file("../series/$tvId/$seasonId/$episodeId.mp4")
            ->setCache([
                'no_cache' => true,
                'no_store' => true,
                'must_revalidate' => true,
            ])
        ;
    }
}
