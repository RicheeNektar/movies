<?php

namespace App\Controller;

use App\Form\MovieSearchType;
use App\Form\RequestMovieType;
use App\Repository\MovieBackdropRepository;
use App\Repository\MovieRepository;
use App\Repository\RequestRepository;
use App\Repository\SeriesRepository;
use App\Repository\UserRepository;
use App\Service\MovieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RequestController extends AbstractController
{
    private MovieRepository $movieRepository;
    private SeriesRepository $seriesRepository;
    private HttpClientInterface $tmdbClient;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private MovieService $movieService;
    private RequestRepository $requestRepository;

    public function __construct(
        MovieRepository $movieRepository,
        SeriesRepository $seriesRepository,
        HttpClientInterface $tmdbClient,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        MovieService $movieService,
        RequestRepository $requestRepository
    ) {
        $this->movieRepository = $movieRepository;
        $this->seriesRepository = $seriesRepository;
        $this->tmdbClient = $tmdbClient;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->movieService = $movieService;
        $this->requestRepository = $requestRepository;
    }

    /**
     * @Route("/request/{tmdbId<\d+>}", name="request")
     */
    public function request(UserInterface $iUser, int $tmdbId)
    {
        $user = $this->userRepository->findOneBy(['username' => $iUser->getUserIdentifier()]);

        if (null !== $user && $this->movieRepository->findOneBy([
            'isHidden' => false,
            'id' => $tmdbId,
        ]) !== null) {
            return $this->redirectToRoute('movie-player', [
                'movie' => $tmdbId,
            ]);

        } else if ($this->seriesRepository->find($tmdbId) !== null) {
            return $this->redirectToRoute('seasons', [
                'series' => $tmdbId,
            ]);

        } else {
            $movie = $this->movieRepository->find($tmdbId);

            if (null === $movie || null === $this->requestRepository->findOneBy([
                'user' => $user,
                'movie' => $movie,
            ])) {
                $movie = $this->movieService->findById($tmdbId);

                if (null !== $movie) {
                    $request = new \App\Entity\Request();
                    $request->setMovie($movie);
                    $request->setUser($user);

                    $this->entityManager->persist($request);
                    $this->entityManager->flush();

                    return $this->redirectToRoute('request-page', [
                        'status' => 'requested',
                    ]);
                }
            } else {
                return $this->redirectToRoute('request-page', [
                    'status' => 'already_requested',
                ]);
            }
        }

        return $this->redirectToRoute('request-page');
    }

    /**
     * @Route("/request-page", name="request-page")
     */
    public function index(Request $request): Response
    {
        $page = min(0, $request->query->get('page') ?? 0);
        $status = $request->query->get('status', '');

        $form = $this->createForm(RequestMovieType::class);
        $form->handleRequest($request);

        $parameters = [
            'backdrop' => $backdrop ?? null,
            'movie_search' => $form,
            'first_page' => $page === 0,
            'page' => $page,
            'last_page' => true,
            'search_results' => [],
            'total_pages' => 0,
            'status' => $status,
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
                $parameters['total_pages'] = $data['total_pages'];
            } else {
                $parameters['error'] = $response->getStatusCode();
            }
        }

        return $this->renderForm('request/index.html.twig', $parameters);
    }
}
