<?php

namespace App\Repository;

use App\Entity\MovieBackdrop;
use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MovieBackdrop|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovieBackdrop|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovieBackdrop[]    findAll()
 * @method MovieBackdrop[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieBackdropRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovieBackdrop::class);
    }

    public function findRandomBackdropFor(Movie $movie): ?MovieBackdrop
    {
        $backdrops = $this->findBy([
            'movie' => $movie,
        ]);

        if (count($backdrops) === 0) {
            $backdrops = $this->findAll();
        }

        if (count($backdrops) === 0) {
            return null;
        }

        return $backdrops[random_int(0, count($backdrops) - 1)];
    }
}
