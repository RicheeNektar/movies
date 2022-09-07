<?php

namespace App\Repository;

use App\Entity\LoginCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Tests\Models\Cache\Login;

/**
 * @method LoginCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoginCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoginCode[]    findAll()
 * @method LoginCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoginCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginCode::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(LoginCode $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(LoginCode $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findUnexpiredById(int $id): LoginCode
    {
        return $this->createQueryBuilder('lc')
            ->andWhere('lc.createdAt > :expire')
            ->setParameter('expire', new \DateTimeImmutable('-15 Minutes'))
            ->andWhere('lc.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }
}
