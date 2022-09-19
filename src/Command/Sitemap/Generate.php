<?php

namespace App\Command\Sitemap;

use App\Entity\AbstractMedia;
use App\Repository\EpisodeRepository;
use App\Repository\MovieRepository;
use SimpleXMLElement;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\HttpUtils;

class Generate extends Command
{
    protected static $defaultDescription = 'Generates a sitemap based on all movies, series, seasons and episodes';
    protected static $defaultName = 'app:sitemap:generate';

    private MovieRepository $movieRepository;
    private EpisodeRepository $episodeRepository;
    private RouterInterface $router;
    private KernelInterface $kernel;

    public function __construct(
        MovieRepository $movieRepository,
        EpisodeRepository $episodeRepository,
        RouterInterface $router,
        KernelInterface $kernel
    ) {
        parent::__construct();
        $this->movieRepository = $movieRepository;
        $this->episodeRepository = $episodeRepository;
        $this->router = $router;
        $this->kernel = $kernel;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $root = new SimpleXMLElement('<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="https://www.google.com/schemas/sitemap-video/1.1" />');

        $imagePackage = new PathPackage("/images", new EmptyVersionStrategy());

        foreach ($this->movieRepository->findAll() as $movie) {
            $xmlUrl = $root->addChild('url');
            $xmlUrl->addChild('loc', $this->router->generate('movie-player', ['movie' => $movie->getId()]));
            $xmlUrl->addChild('lastmod', $movie->getCreationDate()->format(DATE_ISO8601));

            $xmlVideo = $xmlUrl->addChild('video:video');
            $xmlVideo->addChild('video:title', $movie->getTitle());
            $xmlVideo->addChild('video:thumbnail_loc', $imagePackage->getUrl("/{$movie->getAsset()}.webp"));
            $xmlVideo->addChild('video:publication_date', $movie->getAirDate()->format(DATE_ISO8601));
        }

        $root->asXML("{$this->kernel->getProjectDir()}/public/sitemap.xml");

        return Command::SUCCESS;
    }
}