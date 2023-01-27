<?php

namespace App\Security;

use App\Repository\LoginCodeRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Date;

class LoginCodeAuthenticator extends AbstractAuthenticator
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

    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST')
            && $request->getPathInfo() === '/login'
            && $request->getContentType() === 'json';
    }

    public function authenticate(Request $request): Passport
    {
        $data = json_decode($request->getContent(), true);

        $passport = new Passport(
            new UserBadge($data['username']),
            new CustomCredentials(
                function (int $codeId, User $user) {
                    $code = $this->loginCodeRepository->findUnusedById($codeId);

                    if (!$code) {
                        return false;
                    }

                    $usedBy = $code->getUsedBy();

                    return $usedBy &&
                        $code->getCreatedAt() > new \DateTimeImmutable("-15 Minutes")
                        && $usedBy->getUserIdentifier() === $user->getUserIdentifier();
                },
                $data['id']
            )
        );

        $passport->addBadge(new RememberMeBadge());
        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $data = json_decode($request->getContent(), true);
        $code = $this->loginCodeRepository->findUnusedById($data['id']);
        $code->setUsedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return new JsonResponse([
            'ok' => true,
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'ok' => false,
            'error' => $exception->getMessageKey(),
        ]);
    }
}