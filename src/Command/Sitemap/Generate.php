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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Generate extends Command
{
    protected static $defaultDescription = 'Generates a sitemap based on all movies, series, seasons and episodes';
    protected static $defaultName = 'app:sitemap:generate';

    private MovieRepository $movieRepository;
    private EpisodeRepository $episodeRepository;
    private UrlGeneratorInterface $router;
    private KernelInterface $kernel;

    public function __construct(
        MovieRepository $movieRepository,
        EpisodeRepository $episodeRepository,
        UrlGeneratorInterface $router,
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

        $root = new SimpleXMLElement('<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" />');

        $baseUrl = "https://richee.video";

        foreach ($this->movieRepository->findAll() as $movie) {
            $xmlUrl = $root->addChild('url');
            $xmlUrl->addChild('loc', "$baseUrl/movie/{$movie->getId()}");
            $xmlUrl->addChild('lastmod', $movie->getCreationDate()->format(DATE_ATOM));

            $xmlVideo = $xmlUrl->addChild('video:video', null, 'https://www.google.com/schemas/sitemap-video/1.1/sitemap-video.xsd');
            $xmlVideo->addChild('video:title', $movie->getTitle());
            $xmlVideo->addChild('video:thumbnail_loc', "$baseUrl/images/{$movie->getAsset()}.webp");
            $xmlVideo->addChild('video:publication_date', $movie->getAirDate()->format(DATE_ATOM));
        }

        $doc = dom_import_simplexml($root)->ownerDocument;
        $doc->encoding = 'utf-8';

        $root->saveXML("{$this->kernel->getProjectDir()}/public/sitemap.xml");

        return Command::SUCCESS;
    }
}
