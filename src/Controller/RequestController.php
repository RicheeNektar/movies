<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\RequestMovieType;
use App\Repository\MovieRepository;
use App\Repository\RequestRepository;
use App\Repository\UserRepository;
use App\Service\MovieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RequestController extends AbstractController
{
    private MovieRepository $movieRepository;
    private HttpClientInterface $tmdbClient;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private MovieService $movieService;
    private RequestRepository $requestRepository;
    private TranslatorInterface $translator;

    public function __construct(
        MovieRepository $movieRepository,
        HttpClientInterface $tmdbClient,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        MovieService $movieService,
        RequestRepository $requestRepository,
        TranslatorInterface $translator
    ) {
        $this->movieRepository = $movieRepository;
        $this->tmdbClient = $tmdbClient;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->movieService = $movieService;
        $this->requestRepository = $requestRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/request/{tmdbId<\d+>}", name="request-page")
     */
    public function request(UserInterface $iUser, int $tmdbId): Response
    {
        $user = $this->userRepository->findOneBy(['username' => $iUser->getUserIdentifier()]);

        if (null !== $user && $this->movieRepository->findOneBy([
            'isHidden' => false,
            'id' => $tmdbId,
        ]) !== null) {
            return $this->redirectToRoute('movie-player', [
                'movie' => $tmdbId,
            ]);
        }

        $movie = $this->movieRepository->find($tmdbId);

        if (null === $movie) {
            $movie = $this->movieService->findById($tmdbId);
        } else if (null !== $this->requestRepository->findOneBy([
            'user' => $user,
            'movie' => $movie,
        ])) {
            return $this->redirectToRoute('request', [
                'status' => 'already_requested',
            ]);
        }

        $request = new \App\Entity\Request();
        $request->setMovie($movie);
        $request->setUser($user);

        foreach ($this->userRepository->findAllAdmins() as $admin) {
            $message = new Message();
            $message->setUser($admin);
            $message->setTitle($this->translator->trans('messages.new_request.title'));
            $message->setText($this->translator->trans('messages.new_request.text', [
                'username' => $user->getUserIdentifier(),
                'title' => $movie->getTitle()
            ]));

            $this->entityManager->persist($message);
        }

        $this->entityManager->persist($request);
        $this->entityManager->flush();

        return $this->redirectToRoute('request', [
            'status' => 'requested',
        ]);
    }

    /**
     * @Route("/request", name="request")
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
