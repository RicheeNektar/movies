<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Movie;
use App\Entity\Season;
use App\Entity\Series;
use App\Entity\User;
use App\Repository\EpisodeRepository;
use App\Repository\MovieBackdropRepository;
use App\Repository\SeriesBackdropRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class PlayerController extends AbstractController
{
    private SeriesBackdropRepository $seriesBackdropRepository;
    private MovieBackdropRepository $movieBackdropRepository;
    private EpisodeRepository $episodeRepository;
    private UserRepository $userRepository;

    public function __construct(
        SeriesBackdropRepository $seriesBackdropRepository,
        MovieBackdropRepository $movieBackdropRepository,
        EntityManagerInterface $entityManager,
        ContainerBagInterface $containerBag,
        EpisodeRepository $episodeRepository,
        UserRepository $userRepository
    ) {
        parent::__construct($entityManager, $containerBag);
        $this->seriesBackdropRepository = $seriesBackdropRepository;
        $this->movieBackdropRepository = $movieBackdropRepository;
        $this->episodeRepository = $episodeRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/movie/{movie<\d+>}", name="movie-player")
     */
    public function moviePlayer(?UserInterface $iUser, Request $request, Movie $movie): Response
    {
        if ($iUser instanceof User) {
            if ($request->query->getBoolean('watched', false) === true) {
                $iUser->addWatchedMovie($movie);
                $this->entityManager->flush();

                return $this->json(['success' => 'true']);
            }

            $backdrop = $this->movieBackdropRepository->findRandomBackdropFor($movie);
            $this->generateWatchToken($iUser);

            return $this->render('player/movies.html.twig', [
                'movie' => $movie,
                'backdrop' => $backdrop,
            ]);
        }

        return $this->denyAccess();
    }

    /**
     * @Route("/tv/{series<\d+>}/{season<\d+>}/{episode<\d+>}", name="tv-player")
     */
    public function tvPlayer(?UserInterface $iUser, Series $series, Season $season, Episode $episode): Response
    {
        if ($iUser instanceof User) {
            $backdrop = $this->seriesBackdropRepository->findRandomBackdropFor($series);

            $episode_count = $this->episodeRepository->count([
                'series' => $series->getId(),
                'season' => $season->getId(),
            ]);

            $this->generateWatchToken($iUser);

            return $this->render('player/series.html.twig', [
                'series' => $series,
                'season' => $season,
                'episode' => $episode,
                'episode_count' => $episode_count,
                'backdrop' => $backdrop,
            ]);
        }

        return $this->denyAccess();
    }

    private function denyAccessOrResponse(Request $request, ?UserInterface $user, Response $response): Response
    {
        if (null === $user) {
            $token = $request->query->get('token', '');
            $user = $this->userRepository->findOneBy(['accessToken' => $token]);

            if (null === $user) {
                return $this->denyAccess();
            }
        }

        return $response;
    }

    /**
     * @Route("/movie/{movie<\d+>}/file", name="movie-file")
     */
    public function movieFile(Movie $movie, Request $request, ?UserInterface $user): Response
    {
        return $this->denyAccessOrResponse(
            $request,
            $user,
            $this->file("../movies/" . $movie->getId() . ".mp4")
                ->setCache([
                    'no_cache' => true,
                    'no_store' => true,
                    'must_revalidate' => true,
                ]
            )
        );
    }

    /**
     * @Route("/tv/{series<\d+>}/{season<\d+>}/{episode<\d+>}/file", name="tv-file")
     */
    public function tvFile(Series $series, Season $season, Episode $episode, Request $request, ?UserInterface $user): Response
    {
        $tvId = $series->getId();
        $seasonId = $season->getId();
        $episodeId = $episode->getId();

        return $this->denyAccessOrResponse(
            $request,
            $user,
            $this->file("../series/$tvId/$seasonId/$episodeId.mp4")
                ->setCache([
                    'no_cache' => true,
                    'no_store' => true,
                    'must_revalidate' => true,
                ]
            )
        );
    }
}
