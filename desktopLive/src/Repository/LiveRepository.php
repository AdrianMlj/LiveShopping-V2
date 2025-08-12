<?php

namespace App\Repository;

use App\Entity\Live;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Live>
 */
class LiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Live::class);
    }


    /**
     * Retourne les lives en cours (endLive IS NULL), récents d'abord.
     * @return Live[]
     */
    public function findOnGoingLives(): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.endLive IS NULL')
            ->orderBy('l.startLive', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveLives(): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.endLive IS NULL')
            ->join('l.seller', 's')
            ->addSelect('s')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Live[] Returns an array of Live objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Live
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
