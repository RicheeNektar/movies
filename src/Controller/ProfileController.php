<?php

namespace App\Controller;

use App\Form\UpdatePasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile", name="profile_")
 */
class ProfileController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request): Response
    {
        $updatePassword = $this->createForm(UpdatePasswordType::class);
        $updatePassword->handleRequest($request);

        if ($updatePassword->isSubmitted() && $updatePassword->isValid()) {
            $user = $this->userRepository->findOneBy([
                'username' => $this->getUser()->getUserIdentifier(),
            ]);

            $data = $updatePassword->getData();

            if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
                $updatePassword->addError(new FormError('update_password.old.invalid'));
            }

            if (count($updatePassword->getErrors()) === 0) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $data['new']));
                $this->entityManager->flush();
            }
        }

        return $this->renderForm('profile/index.html.twig', [
            'update_password_form' => $updatePassword,
            'data' => $data ?? [],
            'errors' => $updatePassword->getErrors(),
        ]);
    }
}
