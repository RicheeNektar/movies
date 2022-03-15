<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movie[]    findAll()
 * @method Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Movie::class);
    }

    private function onlyVisible(QueryBuilder $qb): QueryBuilder
    {
        return $qb->andWhere('b.isHidden = false');
    }

    /**
     * @inheritDoc
     */
    public function count(array $criteria = []): int
    {
        $movies = $this->findBy(array_merge($criteria, [
                'isHidden' => false,
        ]));

        return count($movies);
    }

    public function findRandomMovie(): ?Movie
    {
        $movies = $this->findBy([
            'isHidden' => false,
        ]);
        $count = count($movies);

        if ($count === 0) {
            return null;
        }

        return $movies[random_int(0, $count - 1)];
    }

    public function findMoviesOnPage(int $page)
    {
        return $this->onlyVisible(
            $this->paginate($this->createQueryBuilder('b'), $page)
        )
            ->orderBy('b.title')
            ->getQuery()
            ->getResult();
    }

    public function findMoviesWithTitleLike(string $title, int $page)
    {
        return $this->onlyVisible(
            $this->paginate($this->createQueryBuilder('b'), $page)
        )
            ->andWhere('b.title LIKE CONCAT(:title, \'%\')')
            ->orderBy('b.title')
            ->setParameters([
                'title' => $title,
            ])
            ->getQuery()
            ->getResult();
    }

    public function findLatestMovies(int $limit)
    {
        return $this->onlyVisible($this->createQueryBuilder('b'))
            ->orderBy('b.creationDate', 'DESC')
            ->addOrderBy('b.title')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
