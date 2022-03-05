<?php

namespace App\Twig;

use Symfony\Component\Security\Core\Security;
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
        ];
    }

    public function isAdmin(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}