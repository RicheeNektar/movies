<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\RequestRepository;
use App\Repository\UserRepository;
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
    private UserPasswordHasherInterface $userPasswordHasher;
    private EntityManagerInterface $entityManager;
    private RequestRepository $requestRepository;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        RequestRepository $requestRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->entityManager = $entityManager;
        $this->requestRepository = $requestRepository;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 0);
        $totalPages = $this->userRepository->countPages();

        $registerUserForm = $this->createForm(RegistrationType::class);
        $registerUserForm->handleRequest($request);

        $status = $request->query->get('status', '');

        if ($registerUserForm->isSubmitted() && $registerUserForm->isValid()) {
            $registerUserFormData = $registerUserForm->getData();

            $roles = ['ROLE_USER'];

            if ($registerUserFormData['isAdmin']) {
                $roles[] = 'ROLE_ADMIN';
            }

            $user = new User();
            $user->setUsername($registerUserFormData['username']);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $registerUserFormData['password']));
            $user->setRoles($roles);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $status = 'user_created';
        }

        return $this->render('admin/index.html.twig', [
            'total_pages' => $totalPages,
            'page' => $page,
            'users' => $this->userRepository->findOnPage($page),
            'user_count' => $this->userRepository->count(),
            'messages' => $registerUserForm->getErrors(),
            'register_user_form' => $registerUserForm->createView(),
            'status' => $status,
            'commands' => CommandController::getCommands(),
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