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
    public function getSalesBySeller(int $sellerId, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('s')
            ->join('s.commande', 'c')
            ->join('c.state', 'st')
            ->join('c.client', 'cl')
            ->join('c.seller', 'se')
            ->addSelect('c', 'st', 'cl', 'se')
            ->andWhere('c.seller = :sellerId')
            ->setParameter('sellerId', $sellerId)
            ->orderBy('s.saleDate', 'DESC');

        // Filtre date début
        if (!empty($filters['date_start'])) {
            $qb->andWhere('s.saleDate >= :date_start')
               ->setParameter('date_start', $filters['date_start']);
        }

        // Filtre date fin
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

        return $qb->getQuery()->getResult();
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
