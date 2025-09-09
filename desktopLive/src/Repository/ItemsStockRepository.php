<?php

namespace App\Repository;

use App\Entity\ItemsStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ItemsStock>
 */
class ItemsStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemsStock::class);
    }

    public function findStockMovementByPeriodAndSeller(\DateTimeInterface $startDate, \DateTimeInterface $endDate, int $idSeller): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT s.*, i.name_item, sz.name_size
            FROM items_stock s
            INNER JOIN item_size isz ON s.id_item_size = isz.id_size
            INNER JOIN item i ON isz.id_item = i.id_item
            INNER JOIN size sz ON isz.id_size = sz.id_size
            WHERE s.date_move BETWEEN :start AND :end
            AND i.id_seller = :idSeller
            ORDER BY s.date_move ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'start' => $startDate->format('Y-m-d H:i:s'),
            'end' => $endDate->format('Y-m-d H:i:s'),
            'idSeller' => $idSeller,
        ]);

        return $result->fetchAllAssociative();
    }

    public function findCurrentStockBySeller(int $idSeller): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                i.id_item AS itemId,
                i.name_item AS itemName,
                isz.id_size AS sizeId,
                sz.name_size AS sizeName,
                COALESCE(SUM(s.in_item),0) - COALESCE(SUM(s.out_item),0) AS currentStock
            FROM items_stock s
            INNER JOIN item_size isz ON s.id_item_size = isz.id_size
            INNER JOIN item i ON isz.id_item = i.id_item
            INNER JOIN size sz ON isz.id_size = sz.id_size
            WHERE i.id_seller = :idSeller
            GROUP BY i.id_item, i.name_item, isz.id_size, sz.name_size
            HAVING COALESCE(SUM(s.in_item),0) - COALESCE(SUM(s.out_item),0) > 0
            ORDER BY i.name_item ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'idSeller' => $idSeller,
        ]);

        return $result->fetchAllAssociative();
    }

    //    /**
    //     * @return ItemsStock[] Returns an array of ItemsStock objects
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

    //    public function findOneBySomeField($value): ?ItemsStock
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
