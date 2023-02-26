<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\OrderBy;
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
        return parent::count(array_merge($criteria, [
            'isHidden' => false,
        ]));
    }

    /**
     * @inheritDoc
     */
    public function countPages(array $criteria = []): int
    {
        return parent::countPages(array_merge($criteria, [
            'isHidden' => false,
        ]));
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

    public function findMoviesOnPage(int $page, bool $sortByTitle = false)
    {
        return $this->onlyVisible(
            $this->paginate($this->createQueryBuilder('b'), $page)
        )
            ->orderBy($sortByTitle ? 'b.title' : 'b.creationDate', $sortByTitle ? 'ASC' : 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findMoviesWithTitleLike(string $title, int $page)
    {
        return $this->onlyVisible(
            $this->paginate($this->createQueryBuilder('b'), $page)
        )
            ->andWhere('b.title LIKE :title')
            ->orderBy('b.title')
            ->setParameters([
                'title' => "%$title%",
            ])
            ->getQuery()
            ->getResult();
    }
}
