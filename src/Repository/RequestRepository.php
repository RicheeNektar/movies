<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Entity\Request;
use App\Entity\User;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Request|null find($id, $lockMode = null, $lockVersion = null)
 * @method Request|null findOneBy(array $criteria, array $orderBy = null)
 * @method Request[]    findAll()
 * @method Request[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Request::class);
    }

    public function fineOnPageByUser(User $user, int $page)
    {
        return $this->paginate($this->createQueryBuilder('r'), $page)
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findOnPage(int $page): array
    {
        return $this->paginate($this->createQueryBuilder('r'), $page)
            ->getQuery()
            ->getResult();
    }

    public function findTop10(): array
    {
        return $this->createQueryBuilder('r')
            ->join(Movie::class, 'm', Join::WITH, 'm.id = r.movie')
            ->select([
                'm.id',
                'm.title AS title',
                'COUNT(m.title) AS votes'
            ])
            ->groupBy('r.movie')
            ->orderBy('votes', 'DESC')
            ->addOrderBy('title')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
