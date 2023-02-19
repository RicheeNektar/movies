<?php

namespace App\Twig;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminVerify extends AbstractExtension
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isAdmin', [$this, 'isAdmin']),
            new TwigFunction('isLoggedIn', [$this, 'isLoggedIn']),
        ];
    }

    public function isLoggedIn(): bool
    {
        return $this->security->isGranted('ROLE_USER');
    }

    public function isAdmin(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}