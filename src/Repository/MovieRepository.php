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
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Movie::class);
    }

    private function paginate(QueryBuilder $qb, int $page): QueryBuilder
    {
        return $qb
            ->setFirstResult($page * 8)
            ->setMaxResults(8);
    }

    public function findRandomMovie(): ?Movie
    {
        $movies = $this->findAll();
        $count = count($movies);

        if ($count === 0) {
            return null;
        }

        return $movies[random_int(0, $count - 1)];
    }

    public function findMoviesOnPage(int $page)
    {
        return $this->paginate($this->createQueryBuilder('b'), $page)
            ->orderBy('b.title')
            ->getQuery()
            ->getResult();
    }

    public function findMoviesWithTitleLike(string $title, int $page)
    {
        return $this->paginate($this->createQueryBuilder('b'), $page)
            ->where('b.title LIKE CONCAT(:title, \'%\')')
            ->orderBy('b.title')
            ->setParameters([
                'title' => $title,
            ])
            ->getQuery()
            ->getResult();
    }

    public function findLatestMovies(int $limit)
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
