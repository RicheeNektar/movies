<?php

namespace App\Controller\Api;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    private MessageRepository $messageRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        MessageRepository $messageRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->messageRepository = $messageRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(name="api_list_messages", path="/api/messages/{user<\d+>}/{count<\d+>}", methods="GET")
     */
    public function listMessages(User $user, int $count = 0): Response
    {
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