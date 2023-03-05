<?php

namespace App\Controller\Api;

use App\Entity\LoginCode;
use App\Entity\User;
use App\Repository\LoginCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/login-code", name="api_loginCode_")
 */
class LoginCodeController extends AbstractController
{
    private LoginCodeRepository $loginCodeRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        LoginCodeRepository $loginCodeRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->loginCodeRepository = $loginCodeRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/create", name="create", methods={"POST"})
     */
    public function createLoginCode(): Response
    {
        $code = new LoginCode();
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        return $this->json([
            'id' => $code->getId(),
        ]);
    }

    /**
     * @Route("/verify", name="verify", methods={"POST"})
     */
    public function verify(Request $request): Response
    {
        if ($request->getContentType() !== 'json') {
            return $this->json([
                'ok' => false,
            ]);
        }

        /**
         * @var User $user
         */
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'ok' => false,
            ]);
        }

        $data = json_decode($request->getContent(), true);
        $code = $this->loginCodeRepository->findUnusedById($data['id']);

        if (!$code) {
            return $this->json([
                'ok' => false,
            ]);
        }

        $code->setUsedBy($user);
        $this->entityManager->flush();

        return $this->json([
            'ok' => true,
        ]);
    }

    /**
     * @Route("/check", name="check", methods={"GET"})
     */
    public function checkLoginCode(Request $request): Response
    {
        $id = $request->query->get('id');
        $code = $this->loginCodeRepository->findUnusedById($id);

        if (!$code) {
            return $this->json(['ok' => false, 'message' => 'invalid_id']);
        }

        $usedBy = $code->getUsedBy();
        return $this->json([
            'ok' => true,
            'user' => $usedBy?->getUserIdentifier(),
        ]);
    }
}