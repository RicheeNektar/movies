<?php

namespace App\Controller;

use App\Form\MovieSearchType;
use App\Repository\MovieBackdropRepository;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class IndexController extends AbstractController
{
    private MovieRepository $movieRepository;
    private MovieBackdropRepository $backdropRepository;

    public function __construct(
        MovieRepository $movieRepository,
        MovieBackdropRepository $backdropRepository
    ) {
        $this->movieRepository = $movieRepository;
        $this->backdropRepository = $backdropRepository;
    }

    /**
     * @Route("/", name="movies")
     */
    public function index(Request $request): Response
    {
        $totalPages = $this->movieRepository->countPages();
        $page = $request->query->get('page', 0);

        $searchForm = $this->createForm(MovieSearchType::class, null, [
            'method' => 'GET',
        ]);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $movieSearch = $searchForm->getData();
            if ($movieSearch['query']) {
                $movies = $this->movieRepository->findMoviesWithTitleLike($movieSearch['query'], $page);
            }
        }

        if (!isset($movies)) {
            $movies = $this->movieRepository->findMoviesOnPage($page);
        }

        if (count($movies) > 0) {
            $backdrop = $this->backdropRepository->findRandomBackdropFor($movies[random_int(0, count($movies) - 1)]);
        }

        return $this->render('movies/index.html.twig', [
            'backdrop' => $backdrop ?? null,
            'movie_count' => $this->movieRepository->count(),
            'total_pages' => $totalPages,
            'page' => $page,
            'movies' => $movies,
            'movie_search' => $searchForm->createView(),
        ]);
    }
}
