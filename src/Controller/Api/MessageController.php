<?php

namespace App\Controller\Api;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class MessageController extends AbstractController
{
    private MessageRepository $messageRepository;
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(
        MessageRepository $messageRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->messageRepository = $messageRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @Route(name="api_list_messages", path="/api/messages/{count<\d+>}", methods="GET")
     */
    public function listMessages(int $count = 0): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if ($count > 0) {
            return $this->json($this->messageRepository->findMaxForUser($user, $count));
        }

        return $this->json([]);
    }

    /**
     * @Route(name="api_ack_message", path="/api/messages/ack/{message<\d+>}", methods="POST")
     */
    public function acknowledgeMessage(Message $message = null): Response
    {
        if (null !== $message) {
            $this->entityManager->remove($message);
            $this->entityManager->flush();
        }
        return new Response('', 204);
    }
}