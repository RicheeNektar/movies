<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Entity\Series;
use App\Entity\User;
use App\Form\CreateInviteType;
use App\Form\RegistrationType;
use App\Repository\InvitationRepository;
use App\Repository\RequestRepository;
use App\Repository\UserRepository;
use App\Service\MovieService;
use App\Service\SeriesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private RequestRepository $requestRepository;
    private MovieService $movieService;
    private SeriesService $seriesService;
    private InvitationRepository $invitationRepository;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        RequestRepository $requestRepository,
        MovieService $movieService,
        SeriesService $seriesService,
        InvitationRepository $invitationRepository
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->requestRepository = $requestRepository;
        $this->movieService = $movieService;
        $this->seriesService = $seriesService;
        $this->invitationRepository = $invitationRepository;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 0);
        $totalPages = $this->userRepository->countPages();

        $status = $request->query->getAlnum('status', '');

        $createInviteForm = $this->createForm(CreateInviteType::class);
        $createInviteForm->handleRequest($request);
        $user = $this->getUser();

        if ($createInviteForm->isSubmitted() && $createInviteForm->isValid()) {
            $invitation = new Invitation();
            $invitation->setCreatedBy($user);
            $this->entityManager->persist($invitation);
            $this->entityManager->flush();

            $status = 'invite_created';
        } else {
            $invitation = $this->invitationRepository->findLatestByUser($user);
        }

        $sizes = [
            $this->movieService->getFolderSize(),
            $this->seriesService->getFolderSize(),
        ];

        $free_size = disk_free_space(__DIR__);
        $total_size = disk_total_space(__DIR__);

        $sizes[] = $total_size - $free_size - array_sum($sizes);

        return $this->renderForm('admin/index.html.twig', [
            'total_pages' => $totalPages,
            'page' => $page,
            'users' => $this->userRepository->findOnPage($page),
            'user_count' => $this->userRepository->count(),
            'status' => $status,
            'commands' => CommandController::getCommands(),
            'create_invite_form' => $createInviteForm,
            'sizes_map' => [
                'movies',
                'series',
                'others',
            ],
            'invitation' => $invitation,
            'sizes' => $sizes,
            'total_size' => $total_size,
            'free_size' => $free_size,
            'latest_invite' => $invitation,
        ]);
    }

    /**
     * @Route("/user/{user<\d+>}", name="user")
     */
    public function user(Request $request, User $user): Response
    {
        $page = $request->query->getInt('page', 0);
        $totalPages = $this->requestRepository->countPages([
            'user' => $user,
        ]);

        $requests = $this->requestRepository->fineOnPageByUser($user, $page);

        return $this->render('admin/user/index.html.twig', [
            'page' => $page,
            'total_pages' => $totalPages,
            'requests' => $requests,
            'user' => $user,
        ]);
    }

    /**
     * @Route("/user/{user<\d+>}/delete", name="delete-user")
     */
    public function deleteUser(User $user): Response
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return $this->redirectToRoute('admin_index', [
            'status' => 'user_deleted',
        ]);
    }

    /**
     * @Route(path="/requests", name="requests")
     */
    public function requests(Request $request): Response
    {
        $page = $request->query->getInt('page', 0);
        $totalPages = $this->requestRepository->countPages();

        $requests = $this->requestRepository->findOnPage($page);
        $top10 = $this->requestRepository->findTop10();

        $totalVotes = array_reduce($top10, static function($carry, $item) {
            return $carry + $item['votes'];
        });

        return $this->render('admin/all_requests.html.twig', [
            'top10' => $top10,
            'total_votes' => $totalVotes,
            'page' => $page,
            'total_pages' => $totalPages,
            'requests' => $requests,
        ]);
    }
}