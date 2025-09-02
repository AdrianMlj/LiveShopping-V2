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

}
