<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractRepository extends ServiceEntityRepository
{
    private const ITEMS_PER_PAGE = 8;

    protected function paginate(QueryBuilder $qb, int $page): QueryBuilder
    {
        return $qb
            ->setFirstResult($page * self::ITEMS_PER_PAGE)
            ->setMaxResults(self::ITEMS_PER_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }

    public function countPages(array $criteria = []): int
    {
        return ceil($this->count($criteria) / self::ITEMS_PER_PAGE);
    }
}