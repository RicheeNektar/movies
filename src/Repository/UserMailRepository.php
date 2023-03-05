<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserMail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserMail|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserMail|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserMail[]    findAll()
 * @method UserMail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserMailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMail::class);
    }

    public function add(UserMail $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(UserMail $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getLatestUnverifiedUserMail(User $user): UserMail | null
    {
        return $this->createQueryBuilder('um')
            ->andWhere('um.verifiedAt IS NULL')
            ->andWhere('um.user = :user')
            ->setParameter('user', $user->getId())
            ->orderBy('um.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getLatestVerifiedUserMail(User $user): UserMail | null
    {
        return $this->createQueryBuilder('um')
            ->andWhere('um.verifiedAt IS NOT NULL')
            ->andWhere('um.user = :user')
            ->setParameter('user', $user->getId())
            ->orderBy('um.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
