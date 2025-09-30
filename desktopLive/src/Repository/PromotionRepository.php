<?php

namespace App\Repository;

use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Promotion>
 */
class PromotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    //    /**
    //     * @return Promotion[] Returns an array of Promotion objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Promotion
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Trouve la promotion active pour un article donné
     */
    public function findActivePromotionForItem(int $itemId): ?Promotion
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.item = :itemId')
            ->andWhere('p.startDate <= CURRENT_DATE()')
            ->andWhere('(p.endDate IS NULL OR p.endDate >= CURRENT_DATE())')
            ->setParameter('itemId', $itemId)
            ->orderBy('p.startDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve toutes les promotions actives
     */
    public function findActivePromotions(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.startDate <= CURRENT_DATE()')
            ->andWhere('(p.endDate IS NULL OR p.endDate >= CURRENT_DATE())')
            ->orderBy('p.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
