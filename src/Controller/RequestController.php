<?php

namespace App\Controller;

use App\Form\MovieSearchType;
use App\Form\RequestMovieType;
use App\Repository\MovieBackdropRepository;
use App\Repository\MovieRepository;
use App\Repository\SeriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RequestController extends AbstractController
{
    private MovieRepository $movieRepository;
    private SeriesRepository $seriesRepository;
    private HttpClientInterface $tmdbClient;
    private EntityManagerInterface $entityManager;

    public function __construct(
        MovieRepository $movieRepository,
        SeriesRepository $seriesRepository,
        HttpClientInterface $tmdbClient,
        EntityManagerInterface $entityManager
    ) {
        $this->movieRepository = $movieRepository;
        $this->seriesRepository = $seriesRepository;
        $this->tmdbClient = $tmdbClient;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/request/{tmdbId<\d+>}", name="request")
     */
    public function request(int $tmdbId)
    {
        if ($this->movieRepository->find($tmdbId) !== null) {
            return $this->redirectToRoute('movie-player', [
                'movie' => $tmdbId,
            ]);

        } else if ($this->seriesRepository->find($tmdbId) !== null) {
            return $this->redirectToRoute('seasons', [
                'series' => $tmdbId,
            ]);

        } else {
            $response = $this->tmdbClient->request('GET', "movie/$tmdbId");

            if ($response->getStatusCode() === 200) {
                $request = new \App\Entity\Request();
                $request->setTmdbId($tmdbId);

                $this->entityManager->persist($request);
                $this->entityManager->flush();
            }

            return $this->redirectToRoute('index', [
                'requested' => true,
            ]);
        }
    }

    /**
     * @Route("/request-page", name="request-page")
     */
    public function index(Request $request): Response
    {
        $page = min(0, $request->query->get('page') ?? 0);

        $form = $this->createForm(RequestMovieType::class);
        $form->handleRequest($request);

        $parameters = [
            'backdrop' => $backdrop ?? null,
            'movie_search' => $form,
            'first_page' => $page === 0,
            'page' => $page,
            'last_page' => true,
            'search_results' => [],
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $response = $this->tmdbClient->request('GET', 'search/movie', [
                'query' => [
                    'language' => 'de-DE',
                    'query' => urlencode($data['query']),
                    'page' => $page + 1,
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getContent(), true);
                $parameters['search_results'] = $data['results'];
                $parameters['last_page'] = $page === $data['total_pages'];
            } else {
                $parameters['error'] = $response->getStatusCode();
            }
        }

        return $this->renderForm('request/index.html.twig', $parameters);
    }
}
