<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DeleteUserForm;
use App\Form\RegistrationType;
use App\Repository\RequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminUserController extends AbstractController
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
     * @Route("/admin", name="admin")
     */
    public function index(Request $request): Response
    {
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

        $userCount = $this->userRepository->count([]);

        return $this->render('admin/index.html.twig', [
            'user_count' => $userCount,
            'messages' => $registerUserForm->getErrors(),
            'register_user_form' => $registerUserForm->createView(),
            'users' => $this->userRepository->findAll(),
            'status' => $status,
        ]);
    }

    /**
     * @Route("/admin/user/{user<\d+>}", name="user-requests")
     */
    public function userAdministration(Request $request, User $user): Response
    {
        $page = $request->query->getInt('page', 0);

        $totalPages = floor($this->requestRepository->count([
            'user' => $user,
        ]) / 8);

        $requests = $this->requestRepository->fineOnPageByUser($user, $page);

        return $this->render('admin/user/index.html.twig', [
            'page' => $page,
            'total_pages' => $totalPages,
            'first_page' => $page == 0,
            'last_page' => $page == $totalPages,
            'requests' => $requests,
        ]);
    }

    /**
     * @Route("/admin/user/{user<\d+>}/delete", name="delete-user")
     */
    public function deleteUser(User $user): Response
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return $this->redirectToRoute('admin', [
            'status' => 'user_deleted',
        ]);
    }
}