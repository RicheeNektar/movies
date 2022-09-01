<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserMail;
use App\Form\RegistrationType;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use App\Service\MailService;
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
    private MailService $mailService;

    public function __construct(
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        InvitationRepository $invitationRepository,
        MailService $mailService
    )
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->invitationRepository = $invitationRepository;
        $this->mailService = $mailService;
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, ): Response
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

            $userMail = new UserMail();
            $userMail->setMail($formData['mail']);
            $userMail->setVerificationCode(random_int(111111, 999999));

            $user->addUserMail($userMail);
            $invitation->setUsedBy($user);

            $this->entityManager->persist($user);
            $this->entityManager->persist($userMail);

            $this->mailService->sendMail(
                $formData['username'],
                $formData['mail'],
                'verification',
                [],
                ['verification_code' => $userMail->getVerificationCode()]
            );

            $this->entityManager->flush();
            return $this->redirectToRoute('verify');
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

    /**
     * @Route("/verify", name="verify")
     */
    public function verify(Request $request)
    {

    }
}