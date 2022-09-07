<?php

namespace App\Security;

use App\Repository\LoginCodeRepository;
use App\Entity\User;
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

class LoginCodeAuthenticator extends AbstractAuthenticator
{
    private LoginCodeRepository $loginCodeRepository;

    public function __construct(
        LoginCodeRepository $loginCodeRepository
    ) {
        $this->loginCodeRepository = $loginCodeRepository;
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
                    $code = $this->loginCodeRepository->find($codeId);

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
        return new JsonResponse([
            'ok' => true,
        ], 200, [

        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'ok' => false,
        ]);
    }
}