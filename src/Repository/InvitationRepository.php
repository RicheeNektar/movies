<?php

namespace App\Repository;

use App\Entity\Invitation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitation[]    findAll()
 * @method Invitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitation::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Invitation $entity, bool $flush = true): void
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
    public function remove(Invitation $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findValid(int $id): Invitation | null
    {
        try {
            return $this->createQueryBuilder('i')
                ->where('i.id = :id')
                ->setParameter('id', $id)
                ->andWhere('i.used_by IS NULL')
                ->andWhere('i.created_at > :date')
                ->setParameter('date', new \DateTimeImmutable("-24 Hours"))
                ->getQuery()
                ->getSingleResult();
        } catch (NonUniqueResultException | NoResultException) {
            return null;
        }
    }

    public function findLatestByUser(User $user): Invitation | null
    {
        return $this->createQueryBuilder('i')
            ->where('i.created_by = :user')
            ->andWhere('i.used_by IS NULL')
            ->andWhere('i.created_at > :date')
            ->setParameter('date', new \DateTimeImmutable("-24 Hours"))
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->orderBy('i.created_at', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
