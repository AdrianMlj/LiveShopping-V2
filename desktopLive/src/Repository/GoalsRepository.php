<?php

namespace App\Repository;

use App\Entity\Goals;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Goals>
 *
 * @method Goals|null find($id, $lockMode = null, $lockVersion = null)
 * @method Goals|null findOneBy(array $criteria, array $orderBy = null)
 * @method Goals[]    findAll()
 * @method Goals[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GoalsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Goals::class);
    }

    public function save(Goals $goal, bool $flush = true): void
    {
        $this->getEntityManager()->persist($goal);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Goals $goal, bool $flush = true): void
    {
        $this->getEntityManager()->remove($goal);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne l'objectif d'un vendeur
     */
    public function findBySeller(int $sellerId): ?Goals
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.seller = :seller')
            ->setParameter('seller', $sellerId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retourne le suivi mensuel des objectifs d'un vendeur
     *
     * @param int $sellerId
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @return array
     */
    public function getMonthlyGoalsProgress(int $sellerId, \DateTime $dateDebut, \DateTime $dateFin): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                EXTRACT(YEAR FROM s.sale_date) AS annee,
                EXTRACT(MONTH FROM s.sale_date) AS mois,
                g.target_ca,
                g.target_ventes,
                COALESCE(SUM(cd.price * cd.quantity), 0) AS ca_realise,
                COUNT(DISTINCT s.id_sale) AS ventes_realisees,
                COALESCE(SUM(cd.price * cd.quantity), 0) - g.target_ca AS ecart_ca,
                COUNT(DISTINCT s.id_sale) - g.target_ventes AS ecart_ventes
            FROM Goals g
            LEFT JOIN Commande c ON c.id_seller = g.id_seller
            LEFT JOIN Sale s ON s.id_commande = c.id_commande AND s.is_paid = TRUE
            LEFT JOIN Commande_details cd ON cd.id_commande = c.id_commande
            WHERE g.id_seller = :sellerId
            AND s.sale_date BETWEEN :dateDebut AND :dateFin
            GROUP BY annee, mois, g.target_ca, g.target_ventes
            ORDER BY annee, mois
        ";

        $result = $conn->executeQuery($sql, [
            'sellerId' => $sellerId,
            'dateDebut' => $dateDebut->format('Y-m-d'),
            'dateFin' => $dateFin->format('Y-m-d'),
        ]);

        return $result->fetchAllAssociative();
    }

    /**
     * Retourne les objectifs mensuels avec projections basés sur la capacité de stock
     *
     * @param int $sellerId
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @param array $stockCapacity
     * @return array
     */
    public function getMonthlyGoalsWithProjections(int $sellerId, \DateTime $dateDebut, \DateTime $dateFin, array $stockCapacity): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // 1) Récupérer les données de vente réalisées
        $sql = "
            SELECT
                EXTRACT(YEAR FROM s.sale_date) AS annee,
                EXTRACT(MONTH FROM s.sale_date) AS mois,
                COALESCE(SUM(cd.price * cd.quantity), 0) AS ca_realise,
                COUNT(DISTINCT s.id_sale) AS ventes_realisees
            FROM Commande c
            INNER JOIN Sale s ON s.id_commande = c.id_commande AND s.is_paid = TRUE
            INNER JOIN Commande_details cd ON cd.id_commande = c.id_commande
            WHERE c.id_seller = :sellerId
            AND s.sale_date BETWEEN :dateDebut AND :dateFin
            GROUP BY annee, mois
            ORDER BY annee, mois
        ";

        $result = $conn->executeQuery($sql, [
            'sellerId' => $sellerId,
            'dateDebut' => $dateDebut->format('Y-m-d'),
            'dateFin' => $dateFin->format('Y-m-d'),
        ])->fetchAllAssociative();

        // 2) Construire la structure avec projections temporelles
        $today = new \DateTime();
        $monthlyGoals = [];

        foreach ($result as $row) {
            $annee = (int)$row['annee'];
            $mois = (int)$row['mois'];
            $dateMois = \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $annee, $mois));
            $jours_total = (int)$dateMois->format('t');
            $moisCourant = $today->format('Y-m');

            $ca_realise = (float)$row['ca_realise'];
            $ventes_realisees = (int)$row['ventes_realisees'];

            // Calcul des projections temporelles
            if ($dateMois->format('Y-m') < $moisCourant) {
                // Mois passé → projection = réalisé
                $projection_ca = $ca_realise;
                $projection_ventes = $ventes_realisees;
            } elseif ($dateMois->format('Y-m') === $moisCourant) {
                // Mois en cours → projection basée sur jours écoulés
                $jours_passes = (int)$today->format('d');
                $projection_ca = $jours_passes > 0 ? ($ca_realise / $jours_passes) * $jours_total : 0;
                $projection_ventes = $jours_passes > 0 ? ($ventes_realisees / $jours_passes) * $jours_total : 0;

                // Borne par la capacité stock CA
                if ($stockCapacity['capacity_ca'] > 0) {
                    $projection_ca = min($projection_ca, $stockCapacity['capacity_ca']);
                }
            } else {
                // Mois futur → projection = 0
                $projection_ca = 0;
                $projection_ventes = 0;
            }

            $monthlyGoals[] = [
                'annee' => $annee,
                'mois' => $mois,
                'target_ca' => $stockCapacity['capacity_ca'], // Objectif = capacité stock CA
                'target_ventes' => (int)$stockCapacity['capacity_units'], // Objectif = capacité stock unités
                'ca_realise' => $ca_realise,
                'ventes_realisees' => $ventes_realisees,
                'projection_ca' => $projection_ca,
                'projection_ventes' => (int)round($projection_ventes),
                'ecart_ca' => $ca_realise - $stockCapacity['capacity_ca'],
                'ecart_ventes' => $ventes_realisees - (int)$stockCapacity['capacity_units'],
            ];
        }

        return $monthlyGoals;
    }

}
