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
                'i.description AS item_description',
                'i.images AS images',
                'c.id AS category_id',
                'c.nameCategory AS category_name',
                'isize.id AS size_id',
                'isize.valueSize AS size_value',
                'color.id AS color_id',
                'color.nameColor AS color_name',
                'MIN(isc.images) AS color_image',
                $availableExpr . ' AS qty_available',
                'promo.id AS promotion_id',
                'promo.namePromotion AS promotion_name',
                'promo.percentage AS promotion_percentage',
                'promo.description AS promotion_description',
                'promo.startDate AS promotion_start_date',
                'promo.endDate AS promotion_end_date',
                'price.price AS original_price'
            )
            ->join('i.category', 'c')
            ->join('i.itemSizes', 'isize')
            ->join('isize.itemSizeColors', 'isc')
            ->join('isc.stocks', 's')
            ->join('isc.color', 'color')
            ->leftJoin('i.priceItems', 'price', 'WITH',
                'price.datePrice = (SELECT MAX(p2.datePrice) FROM App\Entity\PriceItems p2 WHERE p2.item = i.id)')
            ->leftJoin('i.promotions', 'promo', 'WITH',
                'promo.startDate <= CURRENT_DATE() AND (promo.endDate IS NULL OR promo.endDate >= CURRENT_DATE())')
            ->andWhere('IDENTITY(i.seller) = :sellerId')
            ->setParameter('sellerId', $sellerId)
            ->groupBy('i.id, i.nameItem, i.images, c.id, c.nameCategory, isize.id, isize.valueSize, color.id, color.nameColor, promo.id, promo.namePromotion, promo.percentage, promo.description, promo.startDate, promo.endDate, price.price')
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
                    'item_description' => $row['item_description'] ?? null,
                    'image' => $row['images'] ?? null,
                    'category' => [
                        'id' => (int)$row['category_id'],
                        'name' => $row['category_name']
                    ],
                    'sizes' => [],
                    'colors' => [], // Ajout des couleurs groupées au niveau item
                    'promotion' => null // Ajout des promotions
                ];

                // Ajouter les informations de prix
                $grouped[$itemId]['original_price'] = (float)$row['original_price'];

                // Ajouter la promotion si elle existe
                if (!empty($row['promotion_id'])) {
                    $originalPrice = (float)$row['original_price'];
                    $percentage = (float)$row['promotion_percentage'];
                    $discountAmount = $originalPrice * ($percentage / 100);
                    $promoPrice = $originalPrice - $discountAmount;

                    $grouped[$itemId]['promotion'] = [
                        'id' => (int)$row['promotion_id'],
                        'name' => $row['promotion_name'],
                        'percentage' => $percentage,
                        'description' => $row['promotion_description'],
                        'start_date' => $row['promotion_start_date'],
                        'end_date' => $row['promotion_end_date'],
                        'original_price' => $originalPrice,
                        'promo_price' => $promoPrice,
                        'discount_amount' => $discountAmount
                    ];
                }
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
                'color_image' => $row['color_image'] ?? null,
            ];

            // Accumuler la quantité dispo au niveau taille
            $grouped[$itemId]['sizes'][$sizeId]['qty_available'] += (float)$row['qty_available'];

            // Grouper les couleurs au niveau item avec quantité totale
            if (!isset($grouped[$itemId]['colors'][$colorId])) {
                $grouped[$itemId]['colors'][$colorId] = [
                    'color_id' => $colorId,
                    'color_name' => $row['color_name'],
                    'color_image' => $row['color_image'] ?? null,
                    'qty_total' => 0
                ];
            }
            // Accumuler la quantité totale pour cette couleur
            $grouped[$itemId]['colors'][$colorId]['qty_total'] += (float)$row['qty_available'];
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

            // Convertir aussi les couleurs en liste indexée
            $item['colors'] = array_values($item['colors']);

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
