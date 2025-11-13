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

        // 1) Récupérer les données de vente réalisées (toutes périodes)
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
            GROUP BY annee, mois
            ORDER BY annee, mois
        ";

        $allMonths = $conn->executeQuery($sql, [
            'sellerId' => $sellerId,
        ])->fetchAllAssociative();

        if (empty($allMonths)) {
            return [];
        }

        // 2) Normaliser les données mensuelles
        $today = new \DateTime();
        $monthsData = [];

        foreach ($allMonths as $row) {
            $annee = (int)$row['annee'];
            $mois = (int)$row['mois'];
            $dateMois = \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $annee, $mois));

            if (!$dateMois) {
                continue;
            }

            $monthsData[] = [
                'annee' => $annee,
                'mois' => $mois,
                'date' => $dateMois,
                'jours_total' => (int)$dateMois->format('t'),
                'ca_realise' => (float)$row['ca_realise'],
                'ventes_realisees' => (int)$row['ventes_realisees'],
            ];
        }

        usort($monthsData, static function (array $a, array $b): int {
            if ($a['annee'] === $b['annee']) {
                return $a['mois'] <=> $b['mois'];
            }
            return $a['annee'] <=> $b['annee'];
        });

        // 3) Calcul des objectifs lissés + projections
        $monthlyGoals = [];
        $windowDailyCa = [];
        $windowDailySales = [];
        $windowSize = 3;

        $rangeStartKey = (clone $dateDebut)->modify('first day of this month')->format('Y-m');
        $rangeEndKey = (clone $dateFin)->modify('first day of this month')->format('Y-m');

        foreach ($monthsData as $data) {
            $dateMois = $data['date'];
            $monthKey = $dateMois->format('Y-m');
            $annee = $data['annee'];
            $mois = $data['mois'];
            $jours_total = $data['jours_total'];
            $ca_realise = $data['ca_realise'];
            $ventes_realisees = $data['ventes_realisees'];

            $target_ca = $this->computeMedianTarget(
                $windowDailyCa,
                $jours_total,
                (float)($stockCapacity['capacity_ca'] ?? 0),
                $ca_realise,
                $jours_total
            );

            $target_ventes = (int)round($this->computeMedianTarget(
                $windowDailySales,
                $jours_total,
                (float)($stockCapacity['capacity_units'] ?? 0),
                (float)$ventes_realisees,
                $jours_total
            ));

            if ($dateMois->format('Y-m') < $today->format('Y-m')) {
                $projection_ca = $ca_realise;
                $projection_ventes = $ventes_realisees;
            } elseif ($dateMois->format('Y-m') === $today->format('Y-m')) {
                $jours_passes = (int)$today->format('d');
                $projection_ca = $jours_passes > 0 ? ($ca_realise / $jours_passes) * $jours_total : 0.0;
                $projection_ventes = $jours_passes > 0 ? ($ventes_realisees / $jours_passes) * $jours_total : 0.0;

                if (!empty($stockCapacity['capacity_ca'])) {
                    $projection_ca = min($projection_ca, (float)$stockCapacity['capacity_ca']);
                }
            } else {
                $projection_ca = 0.0;
                $projection_ventes = 0.0;
            }

            $dailyCa = $jours_total > 0 ? $ca_realise / $jours_total : 0.0;
            $dailySales = $jours_total > 0 ? $ventes_realisees / $jours_total : 0.0;

            $windowDailyCa[] = $dailyCa;
            $windowDailySales[] = $dailySales;

            if (count($windowDailyCa) > $windowSize) {
                array_shift($windowDailyCa);
            }
            if (count($windowDailySales) > $windowSize) {
                array_shift($windowDailySales);
            }

            if ($monthKey >= $rangeStartKey && $monthKey <= $rangeEndKey) {
                $monthlyGoals[] = [
                    'annee' => $annee,
                    'mois' => $mois,
                    'target_ca' => $target_ca,
                    'target_ventes' => $target_ventes,
                    'ca_realise' => $ca_realise,
                    'ventes_realisees' => $ventes_realisees,
                    'projection_ca' => $projection_ca,
                    'projection_ventes' => (int)round($projection_ventes),
                    'ecart_ca' => $ca_realise - $target_ca,
                    'ecart_ventes' => $ventes_realisees - $target_ventes,
                ];
            }
        }

        return $monthlyGoals;
    }

    /**
     * Calcule un objectif mensuel basé sur la médiane des moyennes journalières.
     *
     * @param array<int,float> $dailyWindow
     */
    private function computeMedianTarget(
        array $dailyWindow,
        int $daysInMonth,
        float $fallbackTarget,
        float $currentTotal,
        int $currentDays
    ): float {
        if (!empty($dailyWindow)) {
            $medianDaily = $this->median($dailyWindow);
            return round($medianDaily * $daysInMonth, 2);
        }

        if ($fallbackTarget > 0) {
            return round($fallbackTarget, 2);
        }

        if ($currentDays > 0) {
            $dailyAverage = $currentTotal / $currentDays;
            return round($dailyAverage * $daysInMonth, 2);
        }

        return 0.0;
    }

    /**
     * @param array<int,float> $values
     */
    private function median(array $values): float
    {
        sort($values);
        $count = count($values);

        if ($count === 0) {
            return 0.0;
        }

        $middle = intdiv($count, 2);

        if ($count % 2 === 1) {
            return (float)$values[$middle];
        }

        return (float)(($values[$middle - 1] + $values[$middle]) / 2);
    }
}
