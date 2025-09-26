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

    public function findAvailableItems($user): array
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
            'c.nameCategory',
            $expr . ' AS stock_disponible',
            'p.price AS prix',
            'promo.namePromotion',
            'promo.percentage',
            'COALESCE(i.images, MIN(isc.images)) AS images'
        )
        ->join('i.category', 'c')
        ->join('i.itemSizes', 'isize')
        ->leftJoin('isize.itemSizeColors', 'isc')
        ->join('isc.stocks', 's')
        ->join('i.priceItems', 'p')
        ->leftJoin('i.promotions', 'promo', 'WITH',
            'promo.startDate <= CURRENT_DATE() AND (promo.endDate IS NULL OR promo.endDate >= CURRENT_DATE())'
        )
        ->andWhere('p.datePrice = (' . $sub->getDQL() . ')')
        ->andWhere('i.seller = :user')
        ->setParameter('user', $user)
        ->groupBy('i.id, i.nameItem, c.nameCategory, p.price, promo.namePromotion, promo.percentage')
        ->having($expr . ' > 0')
        ->distinct();

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les articles du vendeur ($sellerId) avec leurs tailles disponibles, couleurs et la catégorie.
     * Si $categoryId est null, retourne les articles de toutes les catégories du vendeur.
     *
     * Structure du retour (array associatif):
     * - item_id
     * - item_name
     * - category_id
     * - category_name
     * - size_id
     * - size_value
     * - color_id
     * - color_name
     * - images (image de l'article)
     * - qty_available
     */
    public function findItemsWithAvailableSizes(int $sellerId, ?int $categoryId = null): array
    {
        $availableExpr = 'SUM(s.inItem - COALESCE(s.outItem, 0))';

        $qb = $this->createQueryBuilder('i')
            ->select(
                'i.id AS item_id',
                'i.nameItem AS item_name',
                'i.images AS images',
                'c.id AS category_id',
                'c.nameCategory AS category_name',
                'isize.id AS size_id',
                'isize.valueSize AS size_value',
                'color.id AS color_id',
                'color.nameColor AS color_name',
                $availableExpr . ' AS qty_available'
            )
            ->join('i.category', 'c')
            ->join('i.itemSizes', 'isize')
            ->join('isize.itemSizeColors', 'isc')
            ->join('isc.stocks', 's')
            ->join('isc.color', 'color')
            ->andWhere('IDENTITY(i.seller) = :sellerId')
            ->setParameter('sellerId', $sellerId)
            ->groupBy('i.id, i.nameItem, c.id, c.nameCategory, isize.id, isize.valueSize, color.id, color.nameColor')
            ->having($availableExpr . ' > 0')
            ->orderBy('c.nameCategory', 'ASC')
            ->addOrderBy('i.nameItem', 'ASC')
            ->addOrderBy('isize.valueSize', 'ASC')
            ->addOrderBy('color.nameColor', 'ASC');

        if ($categoryId !== null) {
            $qb->andWhere('c.id = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Version groupée: items → tailles → couleurs (avec image)
     */
    public function findItemsWithAvailableSizesGrouped(int $sellerId, ?int $categoryId = null): array
    {
        $rows = $this->findItemsWithAvailableSizes($sellerId, $categoryId);

        $grouped = [];
        foreach ($rows as $row) {
            $itemId = (int)$row['item_id'];
            $sizeId = (int)$row['size_id'];
            $colorId = (int)$row['color_id'];

            if (!isset($grouped[$itemId])) {
                $grouped[$itemId] = [
                    'item_id' => $itemId,
                    'item_name' => $row['item_name'],
                    'category' => [
                        'id' => (int)$row['category_id'],
                        'name' => $row['category_name']
                    ],
                    'sizes' => []
                ];
            }

            if (!isset($grouped[$itemId]['sizes'][$sizeId])) {
                $grouped[$itemId]['sizes'][$sizeId] = [
                    'size_id' => $sizeId,
                    'size_value' => $row['size_value'],
                    'qty_available' => 0,
                    'colors' => []
                ];
            }

            $grouped[$itemId]['sizes'][$sizeId]['colors'][$colorId] = [
                'color_id' => $colorId,
                'color_name' => $row['color_name'],
            ];

            // Accumuler la quantité dispo au niveau taille
            $grouped[$itemId]['sizes'][$sizeId]['qty_available'] += (float)$row['qty_available'];
        }

        // Convertir sous-tableaux associatifs en listes indexées pour une sortie plus propre
        $result = [];
        foreach ($grouped as $item) {
            $sizes = [];
            foreach ($item['sizes'] as $size) {
                $size['colors'] = array_values($size['colors']);
                $sizes[] = $size;
            }
            $item['sizes'] = $sizes;
            $result[] = $item;
        }

        return $result;
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
