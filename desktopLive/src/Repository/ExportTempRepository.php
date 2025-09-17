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
}
