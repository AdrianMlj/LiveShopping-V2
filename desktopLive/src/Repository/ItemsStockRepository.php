<?php

namespace App\Repository;

use App\Entity\ItemsStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Category;
use App\Entity\Users;
use App\Entity\Item;
use App\Entity\PriceItems;
use App\Entity\ItemSize;
use App\Entity\Size;
use Doctrine\ORM\EntityManagerInterface;

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
            SELECT
                s.*,
                i.name_item,
                isz.value_size
            FROM items_stock s
            INNER JOIN item_size isz ON s.id_item_size = isz.id_item_size
            INNER JOIN item i ON isz.id_item = i.id_item
            WHERE s.date_move BETWEEN :start AND :end
            AND i.id_seller = :idSeller
            ORDER BY s.date_move ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'start' => $startDate->format('Y-m-d H:i:s'),
            'end'   => $endDate->format('Y-m-d H:i:s'),
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
                isz.id_item_size AS itemSizeId,
                isz.value_size AS valueSize,
                COALESCE(SUM(s.in_item),0) - COALESCE(SUM(s.out_item),0) AS currentStock
            FROM items_stock s
            INNER JOIN item_size isz ON s.id_item_size = isz.id_item_size
            INNER JOIN item i ON isz.id_item = i.id_item
            WHERE i.id_seller = :idSeller
            GROUP BY i.id_item, i.name_item, isz.id_item_size, isz.value_size
            HAVING COALESCE(SUM(s.in_item),0) - COALESCE(SUM(s.out_item),0) > 0
            ORDER BY i.name_item ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'idSeller' => $idSeller,
        ]);

        return $result->fetchAllAssociative();
    }

    public function importCsv(int $idSeller, array $files, EntityManagerInterface $em): string
    {
        $errors = [];
        $result = [];
        $categoryMap = [];
        $itemMap = [];
        $sizeMap = [];
        $itemSizeMap = [];

        // Petite fonction pour ajouter des erreurs
        $addError = function(int $line, string $msg) use (&$errors) {
            $errors[] = "Ligne $line : $msg";
        };

        // ===================== VALIDATORS =====================
        $validateCategoryRow = function(array $row, int $index) use ($addError): bool {
            $valid = true;

            if (count($row) < 3) {
                $addError($index, "Format invalide (attendu: ref, name, description)");
                $valid = false;
            }
            if (empty($row[0])) {
                $addError($index, "Référence de catégorie vide");
                $valid = false;
            }
            if (empty($row[1])) {
                $addError($index, "Nom de catégorie vide");
                $valid = false;
            }
            return $valid;
        };

        $validateItemRow = function(array $row, int $index) use ($addError): bool {
            $valid = true;

            if (count($row) < 6) {
                $addError($index, "Format invalide (attendu: refItem, nameItem, refCategory, images, price, date)");
                $valid = false;
            }

            if (!is_numeric($row[4])) {
                $addError($index, "Prix invalide (pas un nombre) : {$row[4]}");
                $valid = false;
            } elseif ((float)$row[4] < 0) {
                $addError($index, "Prix négatif interdit : {$row[4]}");
                $valid = false;
            }

            if (!\DateTime::createFromFormat('d/m/Y', $row[5])) {
                $addError($index, "Date invalide (attendu format d/m/Y) : {$row[5]}");
                $valid = false;
            }

            if (empty($row[2])) {
                $addError($index, "Référence de catégorie vide pour l’item");
                $valid = false;
            }

            return $valid;
        };

        $validateSizeRow = function(array $row, int $index) use ($addError): bool {
            $valid = true;

            if (count($row) < 4) {
                $addError($index, "Format invalide (attendu: refItem, sizeName, valueSize, inItem)");
                $valid = false;
            }

            if (empty($row[0])) {
                $addError($index, "refItem vide");
                $valid = false;
            }
            if (empty($row[1])) {
                $addError($index, "Nom de taille vide");
                $valid = false;
            }

            if (!is_numeric($row[3])) {
                $addError($index, "Quantité invalide (pas un nombre) : {$row[3]}");
                $valid = false;
            } elseif ((int)$row[3] < 0) {
                $addError($index, "Quantité négative interdite : {$row[3]}");
                $valid = false;
            }

            return $valid;
        };

        // ===================== TRANSACTION =====================
        $em->beginTransaction();
        try {
            // === 1. FILE1 (Catégories) ===
            $file1 = $files[0] ?? null;
            if ($file1 && $file1->isValid()) {
                $csvData = array_map('str_getcsv', file($file1->getPathname()));

                foreach ($csvData as $index => $row) {
                    if ($index === 0) continue;
                    if (!$validateCategoryRow($row, $index)) continue;

                    [$ref, $name, $description] = $row;
                    $category = $em->getRepository(Category::class)->findOneBy([
                        'nameCategory' => $name
                    ]);
                    if (!$category) {
                        $category = new Category();
                        $category->setNameCategory($name);
                        $category->setDescription($description);
                        $em->persist($category);
                        $em->flush();
                    }
                    $categoryMap[$ref] = $category->getId();
                }

                $result['file1'] = $categoryMap;
            } else {
                $addError(0, "Fichier catégories manquant ou invalide");
            }

            // === 2. FILE2 (Items + Prices) ===
            $file2 = $files[1] ?? null;
            $dateFromFile2 = null;
            if ($file2 && $file2->isValid()) {
                $csvData = array_map('str_getcsv', file($file2->getPathname()));

                $seller = $em->getRepository(Users::class)->find($idSeller);
                if (!$seller) {
                    throw new \Exception("Seller introuvable avec ID $idSeller");
                }

                foreach ($csvData as $index => $row) {
                    if ($index === 0) continue;
                    if (!$validateItemRow($row, $index)) continue;

                    [$refItem, $nameItem, $refCategory, $images, $price, $date] = $row;

                    $item = $em->getRepository(Item::class)->findOneBy([
                        'nameItem' => $nameItem,
                        'seller' => $seller
                    ]);

                    if (!$item) {
                        $item = new Item();
                        $item->setNameItem($nameItem);
                        $item->setSeller($seller);
                        $item->setImages($images);

                        if (isset($categoryMap[$refCategory])) {
                            $category = $em->getRepository(Category::class)->find($categoryMap[$refCategory]);
                            if ($category) {
                                $item->setCategory($category);
                            }
                        }

                        $em->persist($item);
                        $em->flush();
                    }

                    $itemMap[$refItem] = $item->getId();

                    $priceItem = new PriceItems();
                    $priceItem->setItem($item);
                    $priceItem->setPrice($price);
                    $priceItem->setDatePrice(\DateTime::createFromFormat('d/m/Y', $date));
                    $dateFromFile2 = \DateTime::createFromFormat('d/m/Y', $date);

                    $em->persist($priceItem);
                    $em->flush();
                }

                $result['file2'] = $itemMap;
            } else {
                $addError(0, "Fichier items manquant ou invalide");
            }

            // === 3. FILE3 (Sizes + Stock) ===
            $file3 = $files[2] ?? null;
            if ($file3 && $file3->isValid()) {
                $csvData = array_map('str_getcsv', file($file3->getPathname()));

                foreach ($csvData as $index => $row) {
                    if ($index === 0) continue;
                    if (!$validateSizeRow($row, $index)) continue;

                    [$refItem, $sizeName, $valueSize, $inItem] = $row;

                    $size = $em->getRepository(Size::class)->findOneBy(['nameSize' => $sizeName]);
                    if (!$size) {
                        $size = new Size();
                        $size->setNameSize($sizeName);
                        $em->persist($size);
                        $em->flush();
                    }
                    $sizeMap[$sizeName] = $size->getId();

                    if (!isset($itemMap[$refItem])) {
                        $addError($index, "Item non trouvé pour refItem=$refItem");
                        continue;
                    }
                    $item = $em->getRepository(Item::class)->find($itemMap[$refItem]);

                    $itemSize = $em->getRepository(ItemSize::class)->findOneBy([
                        'item' => $item,
                        'size' => $size,
                        'valueSize' => $valueSize
                    ]);
                    if (!$itemSize) {
                        $itemSize = new ItemSize();
                        $itemSize->setItem($item);
                        $itemSize->setSize($size);
                        $itemSize->setValueSize($valueSize);
                        $em->persist($itemSize);
                        $em->flush();
                    }
                    $itemSizeMap[$refItem . '-' . $valueSize] = $itemSize->getId();

                    $stock = new ItemsStock();
                    $stock->setItemSize($itemSize);
                    $stock->setOutItem(0);
                    $stock->setInItem($inItem);
                    $stock->setDateMove($dateFromFile2 ?? new \DateTime());

                    $em->persist($stock);
                    $em->flush();
                }

                $result['file3'] = $itemSizeMap;
            } else {
                $addError(0, "Fichier tailles manquant ou invalide");
            }

            // === FIN : commit si aucune erreur ===
            if (!empty($errors)) {
                $em->rollback();
                return "Erreur(s) détectée(s) :\n" . implode("\n", $errors);
            }

            $em->commit();
            return print_r($result, true);

        } catch (\Exception $e) {
            $em->rollback();
            return "Erreur critique : " . $e->getMessage();
        }
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
