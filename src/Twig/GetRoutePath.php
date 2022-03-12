<?php

namespace App\Twig;

use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetRoutePath extends AbstractExtension
{
    private RouterInterface $router;

    public function __construct(
        RouterInterface $router
    ) {
        $this->router = $router;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getRoutePath', [$this, 'getRoutePath'])
        ];
    }

    public function getRoutePath(string $name): string
    {
        return $this->router->getRouteCollection()->get($name)->getPath();
    }
}