<?php

namespace App\Repository;

use App\Entity\Sale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sale::class);
    }

    /**
     * Récupérer la liste des ventes par vendeur avec filtres
     *
     * @param int $sellerId
     * @param array $filters [
     *     'date_start' => \DateTime|null,
     *     'date_end'   => \DateTime|null,
     *     'state'      => int|null,
     *     'is_paid'    => bool|null
     * ]
     */
    // public function getSalesBySeller(int $sellerId, array $filters = []): array
    // {
    //     $qb = $this->createQueryBuilder('s')
    //         ->join('s.commande', 'c')
    //         ->join('c.state', 'st')
    //         ->join('c.client', 'cl')
    //         ->join('c.seller', 'se')
    //         ->leftJoin('c.details', 'cd')
    //         ->addSelect('c', 'st', 'cl', 'se')
    //         // totalAmount caché pour trier par montant
    //         ->addSelect('COALESCE(SUM(cd.price * cd.quantity), 0) AS HIDDEN totalAmount')
    //         ->andWhere('c.seller = :sellerId')
    //         ->setParameter('sellerId', $sellerId)
    //         ->groupBy('s.id, c.id, st.id, cl.id, se.id');

    //     // Filtre date début
    //     if (!empty($filters['date_start'])) {
    //         $qb->andWhere('s.saleDate >= :date_start')
    //            ->setParameter('date_start', $filters['date_start']);
    //     }

    //     // Filtre date fin
    //     if (!empty($filters['date_end'])) {
    //         $qb->andWhere('s.saleDate <= :date_end')
    //            ->setParameter('date_end', $filters['date_end']);
    //     }

    //     // Filtre état de commande
    //     if (!empty($filters['state'])) {
    //         $qb->andWhere('st.id = :stateId')
    //            ->setParameter('stateId', $filters['state']);
    //     }

    //     // Filtre paiement
    //     if (isset($filters['is_paid'])) {
    //         $qb->andWhere('s.isPaid = :isPaid')
    //            ->setParameter('isPaid', $filters['is_paid']);
    //     }

    //     // Tri
    //     $sort = $filters['sort'] ?? 'recent';
    //     if ($sort === 'oldest') {
    //         $qb->orderBy('s.saleDate', 'ASC');
    //     } elseif ($sort === 'amount_asc') {
    //         $qb->orderBy('totalAmount', 'ASC')
    //            ->addOrderBy('s.saleDate', 'ASC');
    //     } elseif ($sort === 'amount_desc') {
    //         $qb->orderBy('totalAmount', 'DESC')
    //            ->addOrderBy('s.saleDate', 'DESC');
    //     } else {
    //         $qb->orderBy('s.saleDate', 'DESC');
    //     }

    //     return $qb->getQuery()->getResult();
    // }

    public function getSalesBySeller(int $sellerId, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('s')
            ->innerJoin('s.commande', 'c')
            ->innerJoin('c.state', 'st')
            ->innerJoin('c.client', 'cl')
            ->innerJoin('c.seller', 'se')
            ->leftJoin('c.details', 'cd')
            ->leftJoin('cd.itemSize', 'isz')
            ->leftJoin('isz.item', 'it')
            ->addSelect('c', 'st', 'cl', 'se', 'cd', 'isz', 'it')
            ->andWhere('c.seller = :sellerId')
            ->setParameter('sellerId', $sellerId);

        // Filtres sur la période
        if (!empty($filters['date_start'])) {
            $qb->andWhere('s.saleDate >= :date_start')
               ->setParameter('date_start', $filters['date_start']);
        }

        if (!empty($filters['date_end'])) {
            $qb->andWhere('s.saleDate <= :date_end')
               ->setParameter('date_end', $filters['date_end']);
        }

        // Filtre état de commande
        if (!empty($filters['state'])) {
            $qb->andWhere('st.id = :stateId')
               ->setParameter('stateId', $filters['state']);
        }

        // Filtre paiement
        if (isset($filters['is_paid'])) {
            $qb->andWhere('s.isPaid = :isPaid')
               ->setParameter('isPaid', $filters['is_paid']);
        }

        // Tri par défaut (récent)
        $qb->orderBy('s.saleDate', 'DESC');

        $sales = $qb->getQuery()->getResult();

        // Calcul des montants pour tri par CA si demandé
        if (!empty($filters['sort']) && \in_array($filters['sort'], ['amount_asc', 'amount_desc'], true)) {
            usort($sales, static function (Sale $a, Sale $b) use ($filters) {
                $totalA = 0.0;
                foreach ($a->getCommande()->getDetails() as $detail) {
                    $totalA += $detail->getPrice() * $detail->getQuantity();
                }

                $totalB = 0.0;
                foreach ($b->getCommande()->getDetails() as $detail) {
                    $totalB += $detail->getPrice() * $detail->getQuantity();
                }

                if (abs($totalA - $totalB) < 0.0001) {
                    // En cas d'égalité on départage sur la date
                    return $filters['sort'] === 'amount_asc'
                        ? $a->getSaleDate() <=> $b->getSaleDate()
                        : $b->getSaleDate() <=> $a->getSaleDate();
                }

                return $filters['sort'] === 'amount_asc'
                    ? $totalA <=> $totalB
                    : $totalB <=> $totalA;
            });
        } elseif (!empty($filters['sort']) && $filters['sort'] === 'oldest') {
            usort($sales, static fn (Sale $a, Sale $b) => $a->getSaleDate() <=> $b->getSaleDate());
        }

        return $sales;
    }

    public function getSaleDetailsById(int $saleId): ?Sale
    {
        $qb = $this->createQueryBuilder('s')
            ->join('s.commande', 'c')
            ->addSelect('c')
            ->join('c.state', 'st')
            ->addSelect('st')
            ->join('c.client', 'cl')
            ->addSelect('cl')
            ->join('c.seller', 'se')
            ->addSelect('se')
            ->join('c.details', 'd')
            ->addSelect('d')
            ->join('d.itemSize', 'item') // <- changer l'alias
            ->addSelect('item')
            ->andWhere('s.id = :saleId')
            ->setParameter('saleId', $saleId);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
