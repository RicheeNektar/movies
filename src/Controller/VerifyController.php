<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\VerifyType;
use App\Repository\UserMailRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class VerifyController extends AbstractController
{
    private UserMailRepository $userMailRepository;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private MailService $mailService;

    public function __construct(
        UserMailRepository $userMailRepository,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        MailService $mailService
    ) {
        $this->userMailRepository = $userMailRepository;
        $this->entityManager = $entityManager;
        $this->mailService = $mailService;
        $this->translator = $translator;
    }

    /**
     * @Route("/verify", name="verify")
     */
    public function index(Request $request): Response
    {
        $verifyForm = $this->createForm(VerifyType::class);
        $verifyForm->handleRequest($request);

        if ($verifyForm->isSubmitted() && $verifyForm->isValid()) {
            /**
             * @var User $user
             */
            $user = $this->getUser();
            $userMail = $this->userMailRepository->getLatestUnverifiedUserMail($user);
            $data = $verifyForm->getData();

            if (!$userMail) {
                if ($this->userMailRepository->getLatestVerifiedUserMail($user) !== null) {
                    $user->addRole('ROLE_VERIFIED');
                    $this->entityManager->flush();

                    return $this->redirectToRoute('movies');
                }
            }

            if ($data['code'] == $userMail->getVerificationCode()) {
                $userMail->setVerifiedAt(new \DateTimeImmutable());
                $user->addRole('ROLE_VERIFIED');

                $this->entityManager->flush();
                $this->mailService->sendMailToUser($user, 'welcome');
                return $this->redirectToRoute('movies');
            } else {
                $verifyForm->addError(new FormError($this->translator->trans('verify.errors.code_invalid')));
            }
        }

        return $this->renderForm('verify/index.html.twig', [
            'verify_form' => $verifyForm,
            'status' => $status ?? 'none',
        ]);
    }
}
