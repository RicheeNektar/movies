<?php

namespace App\Repository;

use App\Entity\Backdrop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @method Backdrop|null find($id, $lockMode = null, $lockVersion = null)
 * @method Backdrop|null findOneBy(array $criteria, array $orderBy = null)
 * @method Backdrop[]    findAll()
 * @method Backdrop[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BackdropRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Backdrop::class);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getRandomBackdrop(): Backdrop|null
    {
        try {
            return $this->createQueryBuilder('b')
                ->orderBy('rand()')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }
}
