<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Messages extends AbstractExtension
{
    private Security $security;
    private UserRepository $userRepository;
    private MessageRepository $messageRepository;

    public function __construct(
        Security $security,
        UserRepository $userRepository,
        MessageRepository $messageRepository
    ) {
        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->messageRepository = $messageRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction("messages", [$this, "getMessages"]),
            new TwigFunction("user", [$this, "getUser"]),
        ];
    }

    public function getMessages(int $limit): array
    {
        $iUser = $this->security->getUser();

        $user = $this->userRepository->findOneBy(['username' => $iUser->getUserIdentifier()]);

        if (null !== $user) {
            return $this->messageRepository->findMaxForUser($user, $limit);
        }

        return [];
    }

    public function getUser(): ?User
    {
        if (!$this->security->getUser()) {
            return null;
        }

        return $this->userRepository->findOneBy(['username' => $this->security->getUser()->getUserIdentifier()]);
    }
}