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
                s.*, i.name_item, isz.value_size, c.name_color
            FROM items_stock s
            INNER JOIN item_size_color isc ON s.id_item_size_color = isc.id_item_size_color
            INNER JOIN color c ON isc.id_color = c.id_color
            INNER JOIN item_size isz ON isc.id_item_size = isz.id_item_size
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
                c.name_color AS colorName,
                isc.id_item_size_color AS itemSizeColorId,
                COALESCE(SUM(s.in_item),0) - COALESCE(SUM(s.out_item),0) AS currentStock
            FROM items_stock s
            INNER JOIN item_size_color isc ON s.id_item_size_color = isc.id_item_size_color
            INNER JOIN color c ON isc.id_color = c.id_color
            INNER JOIN item_size isz ON isc.id_item_size = isz.id_item_size
            INNER JOIN item i ON isz.id_item = i.id_item
            WHERE i.id_seller = :idSeller
            GROUP BY i.id_item, i.name_item, isz.id_item_size, isz.value_size, c.name_color, isc.id_item_size_color
            HAVING COALESCE(SUM(s.in_item),0) - COALESCE(SUM(s.out_item),0) > 0
            ORDER BY i.name_item ASC, isz.value_size ASC, c.name_color ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'idSeller' => $idSeller,
        ]);

        return $result->fetchAllAssociative();
    }

    public function getSellerStockCapacity(int $idSeller): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                SUM(stockQty) AS capacity_units,
                SUM(stockQty * latestPrice) AS capacity_ca
            FROM (
                SELECT
                    i.id_item AS itemId,
                    COALESCE(SUM(s.in_item),0) - COALESCE(SUM(s.out_item),0) AS stockQty,
                    (
                        SELECT p.price
                        FROM price_items p
                        WHERE p.id_item = i.id_item
                        ORDER BY p.date_price DESC
                        LIMIT 1
                    ) AS latestPrice
                FROM items_stock s
                INNER JOIN item_size_color isc ON s.id_item_size_color = isc.id_item_size_color
                INNER JOIN item_size isz ON isc.id_item_size = isz.id_item_size
                INNER JOIN item i ON isz.id_item = i.id_item
                WHERE i.id_seller = :idSeller
                GROUP BY i.id_item
            ) t
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['idSeller' => $idSeller]);
        $row = $result->fetchAssociative();

        return [
            'capacity_units' => (float)($row['capacity_units'] ?? 0),
            'capacity_ca' => (float)($row['capacity_ca'] ?? 0),
        ];
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
            $line = $line + 1;
            $errors[] = "Ligne $line : $msg";
        };

        // ===================== VALIDATORS =====================
        $validateCategoryRow = function(array $row, int $index) use ($addError): bool {
            $valid = true;

            if (count($row) < 3) {
                $addError($index, "Categorie : Format invalide (attendu: ref, name, description)");
                $valid = false;
            }
            if (empty($row[0])) {
                $addError($index, "Categorie : Référence de catégorie vide");
                $valid = false;
            }
            if (empty($row[1])) {
                $addError($index, "Categorie : Nom de catégorie vide");
                $valid = false;
            }
            return $valid;
        };

        $validateItemRow = function(array $row, int $index) use ($addError): bool {
            $valid = true;

            // Accepte 5 ou 6 colonnes (images optionnel)
            if (count($row) < 5) {
                $addError($index, "Items : Format invalide (attendu: refItem, nameItem, refCategory, [images], price, date)");
                $valid = false;
            }

            // Détecter l'index du prix et de la date en fonction de la présence d'images
            $hasImagesColumn = count($row) >= 6;
            $priceIndex = $hasImagesColumn ? 4 : 3;
            $dateIndex = $hasImagesColumn ? 5 : 4;

            if (!is_numeric($row[$priceIndex])) {
                $addError($index, "Items : Prix invalide (pas un nombre) : {$row[$priceIndex]}");
                $valid = false;
            } elseif ((float)$row[$priceIndex] < 0) {
                $addError($index, "Items : Prix négatif interdit : {$row[$priceIndex]}");
                $valid = false;
            }

            if (!\DateTime::createFromFormat('d/m/Y', $row[$dateIndex])) {
                $addError($index, "Items : Date invalide (attendu format d/m/Y) : {$row[$dateIndex]}");
                $valid = false;
            }

            if (empty($row[2])) {
                $addError($index, "Items : Référence de catégorie vide pour l’item");
                $valid = false;
            }

            return $valid;
        };

        $validateSizeRow = function(array $row, int $index) use ($addError): bool {
            $valid = true;

            if (count($row) < 4) {
                $addError($index, "Tailles : Format invalide (attendu: refItemSize, refItem, sizeName, valueSize)");
                $valid = false;
            }

            if (empty($row[0])) {
                $addError($index, "Tailles : refItemSize vide");
                $valid = false;
            }
            if (empty($row[2])) {
                $addError($index, "Tailles : Nom de taille vide");
                $valid = false;
            }
            return $valid;
        };

        $validateColorRow = function(array $row, int $index) use ($addError): bool {
            $valid = true;
            if (count($row) < 3) {
                $addError($index, "Couleurs : Format invalide (attendu: refItemSize, color, inItem)");
                $valid = false;
            }
            if (empty($row[0])) {
                $addError($index, "Couleurs : refItemSize vide");
                $valid = false;
            }
            if (empty($row[1])) {
                $addError($index, "Couleurs : Nom de couleur vide");
                $valid = false;
            }
            if (!is_numeric($row[2])) {
                $addError($index, "Couleurs : Quantité invalide (pas un nombre) : {$row[2]}");
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

            // === 2. FILE2 (Items + Prices [+ Images optionnel]) ===
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

                    // Support deux formats: avec ou sans images
                    $hasImagesColumn = count($row) >= 6;
                    if ($hasImagesColumn) {
                        [$refItem, $nameItem, $refCategory, $images, $price, $date] = $row;
                    } else {
                        [$refItem, $nameItem, $refCategory, $price, $date] = $row;
                        $images = null;
                    }

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

            // === 3. FILE3 (Sizes uniquement) ===
            $file3 = $files[2] ?? null;
            if ($file3 && $file3->isValid()) {
                $csvData = array_map('str_getcsv', file($file3->getPathname()));

                foreach ($csvData as $index => $row) {
                    if ($index === 0) continue;
                    if (!$validateSizeRow($row, $index)) continue;

                    [$refItemSize, $refItem, $sizeName, $valueSize] = $row;

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
                    // Map par refItemSize (nouveau identifiant de la taille dans CSV)
                    $itemSizeMap[$refItemSize] = $itemSize->getId();
                }

                $result['file3'] = $itemSizeMap;
            } else {
                $addError(0, "Fichier tailles manquant ou invalide");
            }

            // === 4. FILE4 (Couleurs + Stock) ===
            $file4 = $files[3] ?? null;
            if ($file4 && $file4->isValid()) {
                $csvData = array_map('str_getcsv', file($file4->getPathname()));

                foreach ($csvData as $index => $row) {
                    if ($index === 0) continue;
                    if (!$validateColorRow($row, $index)) continue;

                    [$refItemSize, $colorName, $inItem] = $row;

                    if (!isset($itemSizeMap[$refItemSize])) {
                        $addError($index, "refItemSize inconnu pour la couleur : $refItemSize");
                        continue;
                    }

                    $itemSize = $em->getRepository(ItemSize::class)->find($itemSizeMap[$refItemSize]);

                    // Créer/trouver la couleur
                    $color = $em->getRepository(\App\Entity\Color::class)->findOneBy(['nameColor' => $colorName]);
                    if (!$color) {
                        $color = new \App\Entity\Color();
                        $color->setNameColor($colorName);
                        $em->persist($color);
                        $em->flush();
                    }

                    // Lier ItemSize et Color
                    $itemSizeColor = $em->getRepository(\App\Entity\ItemSizeColor::class)->findOneBy([
                        'itemSize' => $itemSize,
                        'color' => $color,
                    ]);
                    if (!$itemSizeColor) {
                        $itemSizeColor = new \App\Entity\ItemSizeColor();
                        $itemSizeColor->setItemSize($itemSize);
                        $itemSizeColor->setColor($color);
                        $em->persist($itemSizeColor);
                        $em->flush();
                    }

                    // Créer le mouvement de stock pour cette couleur
                    $stock = new ItemsStock();
                    $stock->setItemSizeColor($itemSizeColor);
                    $stock->setOutItem(0);
                    $stock->setInItem((int)$inItem);
                    $stock->setDateMove($dateFromFile2 ?? new \DateTime());

                    $em->persist($stock);
                    $em->flush();
                }

                $result['file4'] = true;
            } else {
                $addError(0, "Fichier couleurs manquant ou invalide");
            }

            // === FIN : commit si aucune erreur ===
            if (!empty($errors)) {
                $em->rollback();
                return "Erreur(s) détectée(s) :\n" . implode("\n", $errors);
            }

            $em->commit();
            return "true";

        } catch (\Exception $e) {
            $em->rollback();
            return "Erreur critique : " . $e->getMessage();
        }
    }
}
