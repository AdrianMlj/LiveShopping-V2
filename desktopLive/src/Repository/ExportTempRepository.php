<?php

namespace App\Repository;

use App\Entity\ExportTemp;
use App\Entity\ItemSize;
use App\Entity\ItemSizeColor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

class ExportTempRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ExportTemp::class);
        $this->em = $em;
    }

    /**
     * Enregistre les demandes dans export_temp
     *
     * @param array $demandes
     * @throws \Exception
     */
    public function saveDemandes(array $demandes): void
    {
        $this->em->beginTransaction();

        try {
            foreach ($demandes as $demande) {
                $itemSizeColorId = $demande['idItemSize'] ?? null;
                $qty = (int)($demande['qty'] ?? 0);

                if (!$itemSizeColorId || $qty <= 0) continue;

                // Vérifier si cet itemSizeColor existe déjà
                $existing = $this->findOneBy(['itemSizeColor' => $itemSizeColorId]);

                if ($existing) {
                    // Ajouter la quantité si déjà existant
                    $existing->setQuantity($existing->getQuantity() + $qty);
                } else {
                    $itemSizeColor = $this->em->getRepository(ItemSizeColor::class)->find($itemSizeColorId);
                    if (!$itemSizeColor) continue;

                    $export = new ExportTemp();
                    $export->setItemSizeColor($itemSizeColor)
                           ->setQuantity($qty);

                    $this->em->persist($export);
                }
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * Récupère les exports avec jointures utiles (articles, tailles, vendeurs, catégories)
     */
    public function getFullExportData(int $id_seller): array
    {
        return $this->createQueryBuilder('et')
            ->select(
                'et.id',
                'et.quantity',
                'item.id AS itemId',
                'item.nameItem AS itemName',
                'item.images AS itemImage',
                'category.id AS categoryId',
                'category.nameCategory AS categoryName',
                'seller.id AS sellerId',
                'seller.username AS sellerName',
                'size.id AS sizeId',
                'size.nameSize AS sizeLabel',
                'itemSize.valueSize AS valueSize',
                'c.nameColor AS colorName'
            )
            ->join('et.itemSizeColor', 'isc')
            ->join('isc.color', 'c')
            ->join('isc.itemSize', 'itemSize')
            ->join('itemSize.item', 'item')
            ->join('itemSize.size', 'size')
            ->join('item.category', 'category')
            ->join('item.seller', 'seller')
            ->where('seller.id = :id_seller')
            ->setParameter('id_seller', $id_seller)
            ->orderBy('seller.username', 'ASC')
            ->addOrderBy('item.nameItem', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Génère les données CSV à partir des demandes
     *
     * @param array $demandes
     * @return array [categoriesCsv, itemsCsv, sizesCsv]
     */
    public function buildCsvData(array $demandes): array
    {
        // 1️⃣ Récupérer les id_export_temp
        $exportIds = array_column($demandes, 'id');

        // 2️⃣ Récupérer les ExportTemp correspondants
        $exportTemps = $this->em->getRepository(ExportTemp::class)
            ->createQueryBuilder('et')
            ->where('et.id IN (:exportIds)')
            ->setParameter('exportIds', $exportIds)
            ->getQuery()
            ->getResult();

        // 3️⃣ Construire un tableau [id_export_temp => quantity, id_item_size]
        $exportMap = [];
        foreach ($exportTemps as $et) {
            // trouver la quantité envoyée pour ce export_temp
            $quantity = 0;
            foreach ($demandes as $d) {
                if ((int)$d['id'] === $et->getId()) {
                    $quantity = $d['quantity'];
                    break;
                }
            }
            $exportMap[$et->getId()] = [
                'id_item_size' => $et->getItemSizeColor()->getItemSize()->getId(),
                'quantity'     => $quantity
            ];
        }

        // 4️⃣ Préparer le CSV des couleurs (refItemSize, color, inItem)
        $colorCsv = [["refItemSize","color","inItem"]];
        foreach ($exportTemps as $et) {
            $refItemSize = $et->getItemSizeColor()->getItemSize()->getId();
            $colorName   = $et->getItemSizeColor()->getColor()->getNameColor();
            $qty         = $exportMap[$et->getId()]['quantity'] ?? 0;
            $colorCsv[]  = [ $refItemSize, $colorName, $qty ];
        }

        // 5️⃣ Récupérer tous les ItemSize concernés
        $itemSizeIds = array_column($exportMap, 'id_item_size');
        if (empty($itemSizeIds)) {
            return [
                [["refCategory","nameCategory","Description"]],
                [["refItem","nameItem","refCategory","price","date"]],
                [["refItemSize","refItem","size","valueSize","inItem"]],
                $colorCsv
            ];
        }

        $itemSizes = $this->em->getRepository(ItemSize::class)
            ->createQueryBuilder('sz')
            ->leftJoin('sz.item', 'i')->addSelect('i')
            ->leftJoin('i.category', 'c')->addSelect('c')
            ->leftJoin('i.priceItems', 'p')->addSelect('p')
            ->leftJoin('sz.size', 's')->addSelect('s')
            ->where('sz.id IN (:ids)')
            ->setParameter('ids', $itemSizeIds)
            ->getQuery()
            ->getResult();

        // 6️⃣ Préparer les CSV
        $categoriesCsv = [["refCategory","nameCategory","Description"]];
        $itemsCsv      = [["refItem","nameItem","refCategory","price","date"]];
        $sizesCsv      = [["refItemSize","refItem","size","valueSize","inItem"]];

        foreach ($itemSizes as $itemSize) {
            $item = $itemSize->getItem();
            $cat  = $item->getCategory();
            $size = $itemSize->getSize();

            // retrouver la quantité via exportMap
            $quantity = 0;
            foreach ($exportMap as $emap) {
                if ($emap['id_item_size'] === $itemSize->getId()) {
                    $quantity = $emap['quantity'];
                    break;
                }
            }

            // Catégorie
            $categoriesCsv[] = [
                $cat->getId(),
                $cat->getNameCategory(),
                $cat->getDescription()
            ];

            // Dernier prix
            $lastPrice = null;
            $lastDate = null;
            foreach ($item->getPriceItems() as $price) {
                if ($lastDate === null || $price->getDatePrice() > $lastDate) {
                    $lastPrice = $price->getPrice();
                    $lastDate  = $price->getDatePrice();
                }
            }

            $today = (new \DateTime())->format('d/m/Y');
            // Item
            $itemsCsv[] = [
                $item->getId(),
                $item->getNameItem(),
                $cat->getId(),
                $lastPrice ?? 0,
                $today
            ];

            // Taille
            $sizesCsv[] = [
                $itemSize->getId(),
                $item->getId(),
                $size->getNameSize(),
                $itemSize->getValueSize(),
                $quantity
            ];
        }

        return [$categoriesCsv, $itemsCsv, $sizesCsv, $colorCsv];
    }

    public function arrayToCsv(array $data, string $filename): void
    {
        $f = fopen($filename, 'w');
        foreach ($data as $row) {
            fputcsv($f, $row, ",");
        }
        fclose($f);
    }
}
