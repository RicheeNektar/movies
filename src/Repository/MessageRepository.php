<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Message[]|null findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 * @method Message[]|null findAll()
 */
class MessageRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findMaxForUser(User $user, int $limit): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->orderBy('m.createAt')
            ->setMaxResults($limit)
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();
    }
}