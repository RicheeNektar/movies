<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserMailRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailService
{
    private MailerInterface $mailer;
    private TranslatorInterface $translator;
    private UserMailRepository $userMailRepository;

    public function __construct(
        MailerInterface $mailer,
        TranslatorInterface $translator,
        UserMailRepository $userMailRepository
    ) {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->userMailRepository = $userMailRepository;
    }

    public function sendMailToUser(User $user, string $template, array $subjectParams = [], array $params = []): void
    {
        $userMail = $this->userMailRepository->getLatestVerifiedUserMail($user);
        $this->sendMail($user->getUserIdentifier(), $userMail->getMail(), $template, $subjectParams, $params);
    }

    public function sendMail(string $username, string $mail, string $template, array $subjectParams = [], array $params = []): void
    {
        $subject = $this->translator->trans("mail_subjects." . preg_replace('/\//', '.', $template), $subjectParams);

        $message = (new TemplatedEmail())
            ->from(new Address('no-reply@richee.video'))
            ->addTo(new Address($mail, $username))
            ->subject($subject)
            ->htmlTemplate("mail/" . $this->translator->getLocale() . "/$template.html.twig")
            ->context($params);

        $this->mailer->send($message);
    }
}