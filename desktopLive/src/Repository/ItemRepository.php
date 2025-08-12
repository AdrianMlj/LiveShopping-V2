<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\PriceItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function findAvailableItems(): array
    {
        $expr = 'SUM(s.inItem - COALESCE(s.outItem, 0))';
        $sub = $this->getEntityManager()->createQueryBuilder()
            ->select('MAX(p2.datePrice)')
            ->from(PriceItems::class, 'p2')
            ->where('p2.item = i.id');

        $qb = $this->createQueryBuilder('i')
        ->select(
            'i.id AS id_item',
            'i.nameItem',
            'i.images AS images',
            'c.nameCategory',
            $expr . ' AS stock_disponible',
            'p.price AS prix',
            'promo.namePromotion',
            'promo.percentage'
        )
        ->join('i.category', 'c')
        ->join('i.itemSizes', 'isize')
        ->join('isize.stocks', 's')
        ->join('i.priceItems', 'p')
        ->leftJoin('i.promotions', 'promo', 'WITH',
            'promo.startDate <= CURRENT_DATE() AND (promo.endDate IS NULL OR promo.endDate >= CURRENT_DATE())'
        )
        ->andWhere('p.datePrice = (' . $sub->getDQL() . ')')
        ->groupBy('i.id, i.nameItem, c.nameCategory, p.price, promo.namePromotion, promo.percentage')
        ->having($expr . ' > 0')
        ->distinct();

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Item[] Returns an array of Item objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Item
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
