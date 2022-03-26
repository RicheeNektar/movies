<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    protected EntityManagerInterface $entityManager;
    protected ContainerBagInterface $containerBag;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerBagInterface $containerBag
    ) {
        $this->entityManager = $entityManager;
        $this->containerBag = $containerBag;
    }

    protected function denyAccess(): Response
    {
        return new Response('Access Denied', Response::HTTP_UNAUTHORIZED);
    }

    protected function generateWatchToken(User $user)
    {
        $token = base64_encode(
            hash_hmac(
                'sha256',
                join('|', [
                    $user->getUserIdentifier(),
                    random_int(PHP_INT_MIN, PHP_INT_MAX),
                    (new \DateTimeImmutable())->getTimestamp()
                ]),
                $this->containerBag->get('app.secret')
            )
        );

        $user->setAccessToken($token);
        $this->entityManager->flush();
    }
}