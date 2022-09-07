<?php

namespace App\Controller\Api;

use App\Entity\LoginCode;
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
     * @Route("/check", name="check", methods={"GET"})
     */
    public function checkLoginCode(Request $request): Response
    {
        $id = $request->query->get('id');
        $code = $this->loginCodeRepository->find($id);

        if (!$code) {
            return $this->json(['ok' => false, 'message' => 'invalid_id']);
        }

        $usedBy = $code->getUsedBy();
        return $this->json([
            'ok' => true,
            'user' => $usedBy ? $usedBy->getUserIdentifier() : null,
        ]);
    }
}