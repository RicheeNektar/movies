<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Entity\Series;
use App\Entity\User;
use App\Form\CreateInviteType;
use App\Form\RegistrationType;
use App\Form\UserUpdateType;
use App\Repository\InvitationRepository;
use App\Repository\RequestRepository;
use App\Repository\UserRepository;
use App\Service\MovieService;
use App\Service\SeriesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route(path="/admin/user", name="user_")
 */
class UserController extends AbstractController
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
     * @Route("/{user<\d+>}", name="index", methods={"GET", "POST"})
     */
    public function user(Request $request, User $user): Response
    {
        $form = $this->createForm(UserUpdateType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            if ($formData['is_admin']) {
                $user->addRole('ROLE_ADMIN');
            } else {
                $user->removeRole('ROLE_ADMIN');
            }

            $this->entityManager->flush();
        } else {
            $form->setData([
                'is_admin' => $user->isAdmin(),
            ]);
        }

        $page = $request->query->getInt('page', 0);
        $totalPages = $this->requestRepository->countPages([
            'user' => $user,
        ]);

        $requests = $this->requestRepository->fineOnPageByUser($user, $page);

        return $this->renderForm('admin/user/index.html.twig', [
            'data' => $user->getRoles(),
            'form' => $form,
            'page' => $page,
            'total_pages' => $totalPages,
            'requests' => $requests,
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{user<\d+>}/delete", name="delete")
     */
    public function deleteUser(User $user): Response
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return $this->redirectToRoute('admin_index', [
            'status' => 'user_deleted',
        ]);
    }
}