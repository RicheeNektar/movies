<?php

namespace App\Controller;

use App\Entity\Series;
use App\Repository\SeasonRepository;
use App\Repository\SeriesBackdropRepository;
use App\Repository\SeriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeriesController extends AbstractController
{
    private SeriesRepository $seriesRepository;
    private SeriesBackdropRepository $seriesBackdropRepository;
    private SeasonRepository $seasonRepository;

    public function __construct(
        SeriesRepository $seriesRepository,
        SeriesBackdropRepository $seriesBackdropRepository,
        SeasonRepository $seasonRepository
    ) {
        $this->seriesRepository = $seriesRepository;
        $this->seriesBackdropRepository = $seriesBackdropRepository;
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @Route("/tv", name="series")
     */
    public function seriesList(Request $request): Response
    {
        $seriesCount = $this->seriesRepository->count([]);
        $maxPages = floor($seriesCount / 8);

        $page = max(0, min($maxPages, $request->query->get('page') ?? 0));

        $series = $this->seriesRepository->findOnPage($page);

        if (count($series) > 0) {
            $backdrop = $this->seriesBackdropRepository->findRandomBackdropFor($series[random_int(0, count($series) - 1)]);
        }

        return $this->render('series/index.html.twig', [
            'series' => $series,
            'backdrop' => $backdrop ?? null,
            'series_count' => $seriesCount,
            'first_page' => $page == 0,
            'last_page' => $page == floor($seriesCount / 8),
            'page' => $page,
        ]);
    }

    /**
     * @Route("/tv/{series<\d+>}", name="seasons")
     */
    public function seasonList(Request $request, Series $series): Response
    {
        $page = $request->query->get('page') ?? 0;

        $seasonCount = $this->seasonRepository->count([
            'series' => $series,
        ]);

        $backdrop = $this->seriesBackdropRepository->findRandomBackdropFor($series);

        if ($seasonCount > 0) {
            return $this->render('series/seasons.html.twig', [
                'series' => $series,
                'seasons' => $series->getSeasons(),
                'backdrop' => $backdrop,
                'first_page' => $page == 0,
                'last_page' => $page == floor($seasonCount / 8),
                'page' => $page,
                'season_count' => $seasonCount,
            ]);
        }

        return $this->render('series/no_series.html.twig');
    }
}
