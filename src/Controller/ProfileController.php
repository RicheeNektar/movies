<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserMail;
use App\Form\UpdateMailType;
use App\Form\UpdatePasswordType;
use App\Form\VerifyType;
use App\Repository\UserMailRepository;
use App\Repository\UserRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    private UserMailRepository $userMailRepository;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private UserRepository $userRepository;
    private MailService $mailService;

    public function __construct(
        TranslatorInterface $translator,
        UserPasswordHasherInterface $passwordHasher,
        UserMailRepository $userMailRepository,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        MailService $mailService
    ) {
        $this->translator = $translator;
        $this->userMailRepository = $userMailRepository;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
        $this->mailService = $mailService;
    }

    private function handleUpdatePassword(Request $request, User $user): FormInterface
    {
        $updatePassword = $this->createForm(UpdatePasswordType::class);
        $updatePassword->handleRequest($request);

        if ($updatePassword->isSubmitted() && $updatePassword->isValid()) {
            $data = $updatePassword->getData();

            $user->setPassword($this->passwordHasher->hashPassword($user, $data['new']));
            $this->entityManager->flush();

            $this->mailService->sendMailToUser($user, 'password_change');
        }

        return $updatePassword;
    }

    private function handleUpdateMail(Request $request, User $user): FormInterface
    {
        $form = $this->createForm(UpdateMailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($this->userMailRepository->getLatestUnverifiedUserMail($user) !== null) {
                $form->addError(new FormError($this->translator->trans('update_mail.errors.verification_in_progress')));
                return $form;
            }

            if ($data['mail'] === $this->userMailRepository->getLatestVerifiedUserMail($user)->getMail()) {
                $form->addError(new FormError($this->translator->trans('update_mail.errors.same_as_current')));
                return $form;
            }

            $userMail = new UserMail();
            $userMail->setMail($data['mail']);
            $userMail->setVerificationCode(random_int(111111, 999999));
            $user->addUserMail($userMail);

            $this->mailService->sendMail(
                $user->getUserIdentifier(),
                $data['mail'],
                'mail_change/verification',
                [],
                ['verification_code' => $userMail->getVerificationCode()]
            );

            $this->entityManager->persist($userMail);
            $this->entityManager->flush();
        }
        return $form;
    }

    private function handleVerifyMail(Request $request, User $user): FormInterface
    {
        $form = $this->createForm(VerifyType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userMail = $this->userMailRepository->getLatestUnverifiedUserMail($user);
            $data = $form->getData();

            if ($data['code'] == $userMail->getVerificationCode()) {
                $this->mailService->sendMailToUser($user, 'mail_change/notice');

                $userMail->setVerifiedAt(new \DateTimeImmutable());
                $this->entityManager->flush();
            } else {
                $form->addError(new FormError($this->translator->trans('verify.errors.code_invalid')));
            }
        }

        return $form;
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function index(Request $request): Response
    {
        $user = $this->userRepository->findOneBy([
            'username' => $this->getUser()->getUserIdentifier(),
        ]);

        $updatePasswordForm = $this->handleUpdatePassword($request, $user);
        $updateMailForm = $this->handleUpdateMail($request, $user);
        $verifyMailForm = $this->handleVerifyMail($request, $user);

        $verifiedMail = $this->userMailRepository->getLatestVerifiedUserMail($user);
        $unverifiedMail = $this->userMailRepository->getLatestUnverifiedUserMail($user);

        return $this->renderForm('profile/index.html.twig', [
            'update_password_form' => $updatePasswordForm,
            'update_mail_form' => $updateMailForm,
            'verify_mail_form' => $verifyMailForm,
            'unverified_mail' => $unverifiedMail,
            'mail' => $verifiedMail,
        ]);
    }
}
