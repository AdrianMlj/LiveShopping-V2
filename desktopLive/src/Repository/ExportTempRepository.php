<?php

namespace App\Repository;

use App\Entity\ExportTemp;
use App\Entity\ItemSize;
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
                $itemSizeId = $demande['idItemSize'] ?? null;
                $qty = (int)($demande['qty'] ?? 0);

                if (!$itemSizeId || $qty <= 0) continue;

                // Vérifier si cet itemSize existe déjà
                $existing = $this->findOneBy(['itemSize' => $itemSizeId]);

                if ($existing) {
                    // Ajouter la quantité si déjà existant
                    $existing->setQuantity($existing->getQuantity() + $qty);
                } else {
                    $itemSize = $this->em->getRepository(ItemSize::class)->find($itemSizeId);
                    if (!$itemSize) continue;

                    $export = new ExportTemp();
                    $export->setItemSize($itemSize)
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
                'itemSize.valueSize AS valueSize'
            )
            ->join('et.itemSize', 'itemSize')
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
                'id_item_size' => $et->getItemSize()->getId(),
                'quantity'     => $quantity
            ];
        }

        // 4️⃣ Récupérer tous les ItemSize concernés
        $itemSizeIds = array_column($exportMap, 'id_item_size');
        if (empty($itemSizeIds)) {
            return [
                [["refCategory","nameCategory","Description"]],
                [["refItem","nameItem","refCategory","images","price","date"]],
                [["refItem","size","valueSize","inItem"]]
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

        // 5️⃣ Préparer les CSV
        $categoriesCsv = [["refCategory","nameCategory","Description"]];
        $itemsCsv      = [["refItem","nameItem","refCategory","images","price","date"]];
        $sizesCsv      = [["refItem","size","valueSize","inItem"]];

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
                $item->getImages(),
                $lastPrice ?? 0,
                $today
            ];

            // Taille
            $sizesCsv[] = [
                $item->getId(),
                $size->getNameSize(),
                $itemSize->getValueSize(),
                $quantity
            ];
        }

        return [$categoriesCsv, $itemsCsv, $sizesCsv];
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
