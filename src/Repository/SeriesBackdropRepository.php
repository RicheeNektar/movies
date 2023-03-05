<?php

namespace App\Repository;

use App\Entity\Series;
use App\Entity\SeriesBackdrop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SeriesBackdrop|null find($id, $lockMode = null, $lockVersion = null)
 * @method SeriesBackdrop|null findOneBy(array $criteria, array $orderBy = null)
 * @method SeriesBackdrop[]    findAll()
 * @method SeriesBackdrop[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeriesBackdropRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeriesBackdrop::class);
    }

    public function findRandomBackdropFor(Series $series): SeriesBackdrop
    {
        $backdrops = $this->findBy([
            'series' => $series,
        ]);

        if (count($backdrops) == 0) {
            $backdrops = $this->findAll();
        }

        return $backdrops[random_int(0, count($backdrops) - 1)];
    }
}
