<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegisterController extends AbstractController
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private InvitationRepository $invitationRepository;

    public function __construct(
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        InvitationRepository $invitationRepository
    )
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->invitationRepository = $invitationRepository;
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('movies');
        }

        $registerUserForm = $this->createForm(RegistrationType::class);
        $registerUserForm->handleRequest($request);

        if ($registerUserForm->isSubmitted() && $registerUserForm->isValid()) {
            $formData = $registerUserForm->getData();
            $invitation = $this->invitationRepository->findValid($formData['invitation']);

            if ($invitation === null) {
                return $this->render('register/no_invitation.html.twig');
            }

            $user = new User();
            $user->setUsername($formData['username']);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $formData['password']));
            $user->setRoles(['ROLE_USER']);

            $invitation->setUsedBy($user);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('login');
        }

        $inviteId = $request->query->getInt('id', -1);

        if ($inviteId === -1) {
            return $this->render('register/no_invitation.html.twig');
        }

        $invitation = $this->invitationRepository->findValid($inviteId);

        if ($invitation === null) {
            return $this->render('register/no_invitation.html.twig');
        }

        return $this->renderForm('register/index.html.twig', [
            'invitation' => $invitation,
            'register_user_form' => $registerUserForm,
        ]);
    }
}