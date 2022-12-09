<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CommandController extends AbstractController
{
    private const COMMAND_UPDATE_MOVIES = 'update-movies';
    private const COMMAND_UPDATE_SERIES = 'update-series';
    private const COMMAND_CONVERT_OLD_MOVIES = 'convert-old-movies';
    private const COMMAND_UPDATE_MOVIE_INFOS = 'update-movie-infos';
    private const COMMAND_UPDATE_SERIES_INFOS = 'update-series-infos';

    private Security $security;

    public function __construct(
        Security $security
    ) {
        $this->security = $security;
    }

    /**
     * @Route("/command/{cmd<[\w-]+>}", name="command")
     */
    public function updateMovies(KernelInterface $kernel, Request $request, string $cmd): Response
    {
        if (false === array_search(
                $cmd,
                self::getCommands()
            ) || !$this->security->isGranted('ROLE_ADMIN')
        ) {
            return $this->redirectToRoute('movies');
        }

        $app = new Application($kernel);
        $app->setAutoExit(false);

        $input = new ArrayInput([
            'command' => "app:$cmd",
        ]);

        $output = new BufferedOutput();
        $code = $app->run($input, $output);

        if ($code == 0) {
            return $this->redirectToRoute('admin_index', [
                'status' => 'command_success',
            ]);
        }

        return new Response($output->fetch());
    }

    public static function getCommands(): array
    {
        return [
            self::COMMAND_UPDATE_MOVIES => true,
            self::COMMAND_UPDATE_MOVIE_INFOS => false,
            self::COMMAND_UPDATE_SERIES => false,
            self::COMMAND_UPDATE_SERIES_INFOS => false,
            self::COMMAND_CONVERT_OLD_MOVIES => false,
        ];
    }
}
