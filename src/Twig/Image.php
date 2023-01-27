<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Image extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction("img", [$this, "renderPicture"], ['needs_environment' => true]),
        ];
    }

    public function renderPicture(Environment $environment, string $asset, array $attributes): string
    {
        return $environment->render('twig/picture.html.twig', [
            'asset' => $asset,
            'attributes' => $attributes,
        ]);
    }
}