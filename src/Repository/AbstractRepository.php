<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;


abstract class AbstractRepository extends ServiceEntityRepository
{
    protected function paginate(QueryBuilder $qb, int $page): QueryBuilder
    {
        return $qb
            ->setFirstResult($page * 8)
            ->setMaxResults(8);
    }
}