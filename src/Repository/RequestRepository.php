<?php

namespace App\Repository;

use App\Entity\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Request|null find($id, $lockMode = null, $lockVersion = null)
 * @method Request|null findOneBy(array $criteria, array $orderBy = null)
 * @method Request[]    findAll()
 * @method Request[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Request::class);
    }

    private function paginate(QueryBuilder $qb, int $page): QueryBuilder
    {
        return $qb
            ->setFirstResult($page * 8)
            ->setMaxResults(8);
    }

    public function findOnPage(int $page)
    {
        return $this->paginate($this->createQueryBuilder('r'), $page)
            ->getQuery()
            ->getResult();
    }
}
