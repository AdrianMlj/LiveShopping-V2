<?php

namespace App\Repository;

use App\Entity\Sale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sale>
 */
class SaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sale::class);
    }

    /**
     * Trouve le meilleur vendeur (article) pour une période donnée
     *
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @param int|null $categoryId
     * @return array|null
     */
    public function getBestSellerForPeriod(\DateTime $dateDebut, \DateTime $dateFin, ?int $categoryId): ?array
    {
        $qb = $this->createQueryBuilder('s')
            ->select([
                'i.nameItem',
                'i.images',
                'SUM(pi.price) as chiffre_affaires_total',
                'COUNT(s.id) as nombre_ventes',
                'AVG(pi.price) as prix_moyen'
            ])
            ->innerJoin('s.commande', 'c')
            ->innerJoin('c.bag', 'b')
            ->innerJoin('b.bagDetails', 'bd')
            ->innerJoin('bd.itemSize', 'ish')
            ->innerJoin('ish.item', 'i')
            ->innerJoin('i.priceItems', 'pi')
            ->where('s.saleDate BETWEEN :dateDebut AND :dateFin')
            ->andWhere('s.isPaid = :isPaid')
            ->andWhere('pi.datePrice = (
                SELECT MAX(pi2.datePrice)
                FROM App\Entity\PriceItems pi2
                WHERE pi2.item = i AND pi2.datePrice <= s.saleDate
            )')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->setParameter('isPaid', true)
            ->groupBy('i.id, i.nameItem, i.images')
            ->orderBy('chiffre_affaires_total', 'DESC')
            ->setMaxResults(1);

        if ($categoryId) {
            $qb->innerJoin('i.category', 'cat')
               ->andWhere('cat.id = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result) {
            return [
                'name' => $result['nameItem'],
                'image_id' => $result['images'],
                'sales' => (int)$result['nombre_ventes'],
                'total_revenue' => (float)$result['chiffre_affaires_total'],
                'average_price' => (float)$result['prix_moyen']
            ];
        }

        return null;
    }

    /**
     * Obtient les statistiques mensuelles d'un vendeur pour une période
     *
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @param int $sellerId
     * @return array
     */
    // public function getStatistiquesVendeur(\DateTime $dateDebut, \DateTime $dateFin, int $sellerId): array
    // {
    //     $conn = $this->getEntityManager()->getConnection();

    //     $sql = "
    //         SELECT
    //             EXTRACT(YEAR FROM s.sale_date) AS annee,
    //             EXTRACT(MONTH FROM s.sale_date) AS mois,
    //             COALESCE(SUM(cd.price * cd.quantity), 0) AS chiffre_affaires_mensuel,
    //             COUNT(DISTINCT s.id_sale) AS nombre_commandes_mensuel,
    //             SUM(cd.quantity) AS nombre_articles_vendus_mensuel,
    //             COALESCE(AVG(cd.price), 0) AS prix_moyen_mensuel
    //         FROM sale s
    //         INNER JOIN commande c ON s.id_commande = c.id_commande
    //         INNER JOIN commande_details cd ON cd.id_commande = c.id_commande
    //         INNER JOIN item_size isz ON cd.id_item_size = isz.id_item_size
    //         INNER JOIN item i ON isz.id_item = i.id_item
    //         INNER JOIN users u ON i.id_seller = u.id_user
    //         WHERE u.id_user = :sellerId
    //         AND s.sale_date BETWEEN :dateDebut AND :dateFin
    //         AND s.is_paid = true
    //         GROUP BY annee, mois
    //         ORDER BY annee, mois ASC
    //     ";

    //     $stmt = $conn->executeQuery($sql, [
    //         'sellerId' => $sellerId,
    //         'dateDebut' => $dateDebut->format('Y-m-d'),
    //         'dateFin' => $dateFin->format('Y-m-d'),
    //     ]);

    //     $results = $stmt->fetchAllAssociative();

    //     $labels = [];
    //     $chiffreAffaires = [];
    //     $commandes = [];
    //     $articles = [];
    //     $prixMoyens = [];

    //     foreach ($results as $result) {
    //         $mois = $result['mois'];
    //         $annee = $result['annee'];
    //         $label = \DateTime::createFromFormat('!m', $mois)->format('F') . ' ' . $annee;

    //         if (!in_array($label, $labels)) {
    //             $labels[] = $label;
    //         }

    //         $chiffreAffaires[] = (float) $result['chiffre_affaires_mensuel'];
    //         $commandes[] = (int) $result['nombre_commandes_mensuel'];
    //         $articles[] = (int) $result['nombre_articles_vendus_mensuel'];
    //         $prixMoyens[] = (float) $result['prix_moyen_mensuel'];
    //     }

    //     return [
    //         'labels' => $labels,
    //         'chiffre_affaires' => $chiffreAffaires,
    //         'commandes' => $commandes,
    //         'articles' => $articles,
    //         'prix_moyens' => $prixMoyens,
    //     ];
    // }

    public function getStatistiquesVendeur(\DateTime $dateDebut, \DateTime $dateFin, int $sellerId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // ---- Stats mensuelles (déjà existantes) ----
        $sqlMensuel = "
            SELECT
                EXTRACT(YEAR FROM s.sale_date) AS annee,
                EXTRACT(MONTH FROM s.sale_date) AS mois,
                COALESCE(SUM(cd.price * cd.quantity), 0) AS chiffre_affaires_mensuel,
                COUNT(DISTINCT s.id_sale) AS nombre_commandes_mensuel,
                SUM(cd.quantity) AS nombre_articles_vendus_mensuel,
                COALESCE(AVG(cd.price), 0) AS prix_moyen_mensuel
            FROM sale s
            INNER JOIN commande c ON s.id_commande = c.id_commande
            INNER JOIN commande_details cd ON cd.id_commande = c.id_commande
            INNER JOIN item_size isz ON cd.id_item_size = isz.id_item_size
            INNER JOIN item i ON isz.id_item = i.id_item
            INNER JOIN users u ON i.id_seller = u.id_user
            WHERE u.id_user = :sellerId
            AND s.sale_date BETWEEN :dateDebut AND :dateFin
            AND s.is_paid = true
            GROUP BY annee, mois
            ORDER BY annee, mois ASC
        ";

        $resultsMensuel = $conn->executeQuery($sqlMensuel, [
            'sellerId' => $sellerId,
            'dateDebut' => $dateDebut->format('Y-m-d'),
            'dateFin' => $dateFin->format('Y-m-d'),
        ])->fetchAllAssociative();

        // ---- Stats globales ----
        $sqlGlobal = "
            SELECT
                COALESCE(SUM(cd.price * cd.quantity), 0) AS ca_total,
                COUNT(DISTINCT s.id_sale) AS ventes_total
            FROM sale s
            INNER JOIN commande c ON s.id_commande = c.id_commande
            INNER JOIN commande_details cd ON cd.id_commande = c.id_commande
            INNER JOIN item_size isz ON cd.id_item_size = isz.id_item_size
            INNER JOIN item i ON isz.id_item = i.id_item
            INNER JOIN users u ON i.id_seller = u.id_user
            WHERE u.id_user = :sellerId
            AND s.sale_date BETWEEN :dateDebut AND :dateFin
            AND s.is_paid = true
        ";

        $resultGlobal = $conn->executeQuery($sqlGlobal, [
            'sellerId' => $sellerId,
            'dateDebut' => $dateDebut->format('Y-m-d'),
            'dateFin' => $dateFin->format('Y-m-d'),
        ])->fetchAssociative();

        // ---- Meilleure catégorie ----
        $sqlBestCat = "
            SELECT c.name_category, SUM(cd.price * cd.quantity) AS total_cat
            FROM sale s
            INNER JOIN commande c2 ON s.id_commande = c2.id_commande
            INNER JOIN commande_details cd ON cd.id_commande = c2.id_commande
            INNER JOIN item_size isz ON cd.id_item_size = isz.id_item_size
            INNER JOIN item i ON isz.id_item = i.id_item
            INNER JOIN category c ON i.id_category = c.id_category
            INNER JOIN users u ON i.id_seller = u.id_user
            WHERE u.id_user = :sellerId
            AND s.sale_date BETWEEN :dateDebut AND :dateFin
            AND s.is_paid = true
            GROUP BY c.id_category, c.name_category
            ORDER BY total_cat DESC
            LIMIT 1
        ";

        $bestCategory = $conn->executeQuery($sqlBestCat, [
            'sellerId' => $sellerId,
            'dateDebut' => $dateDebut->format('Y-m-d'),
            'dateFin' => $dateFin->format('Y-m-d'),
        ])->fetchAssociative();

        // ---- Reformater les résultats mensuels ----
        $labels = [];
        $chiffreAffaires = [];
        $commandes = [];
        $articles = [];
        $prixMoyens = [];

        foreach ($resultsMensuel as $result) {
            $label = \DateTime::createFromFormat('!m', $result['mois'])->format('F') . ' ' . $result['annee'];
            $labels[] = $label;
            $chiffreAffaires[] = (float) $result['chiffre_affaires_mensuel'];
            $commandes[] = (int) $result['nombre_commandes_mensuel'];
            $articles[] = (int) $result['nombre_articles_vendus_mensuel'];
            $prixMoyens[] = (float) $result['prix_moyen_mensuel'];
        }

        return [
            // Données globales
            'ca_total' => (float) ($resultGlobal['ca_total'] ?? 0),
            'ventes_total' => (int) ($resultGlobal['ventes_total'] ?? 0),
            'panier_moyen' => ($resultGlobal['ventes_total'] > 0)
                ? $resultGlobal['ca_total'] / $resultGlobal['ventes_total']
                : 0,
            'meilleure_categorie' => $bestCategory['name_category'] ?? 'N/A',

            // Données mensuelles (pour les graphiques)
            'labels' => $labels,
            'chiffre_affaires' => $chiffreAffaires,
            'commandes' => $commandes,
            'articles' => $articles,
            'prix_moyens' => $prixMoyens,
        ];
    }

    /**
     * Obtient les ventes par catégorie pour un vendeur spécifique
     *
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @param int $sellerId
     * @return array
     */
    public function getVentesVendeurParCategorieParMois(\DateTime $dateDebut, \DateTime $dateFin, int $sellerId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                TO_CHAR(s.sale_date, 'YYYY') AS annee,
                TO_CHAR(s.sale_date, 'MM') AS mois,
                cat.name_category,
                SUM(cd.quantity) AS nombre_vendus
            FROM sale s
            INNER JOIN commande c ON s.id_commande = c.id_commande
            INNER JOIN commande_details cd ON cd.id_commande = c.id_commande
            INNER JOIN item_size isz ON cd.id_item_size = isz.id_item_size
            INNER JOIN item i ON i.id_item = isz.id_item
            INNER JOIN category cat ON i.id_category = cat.id_category
            INNER JOIN users seller ON i.id_seller = seller.id_user
            WHERE s.sale_date BETWEEN :dateDebut AND :dateFin
            AND s.is_paid = true
            AND seller.id_user = :sellerId
            GROUP BY annee, mois, cat.name_category
            ORDER BY annee, mois, cat.name_category
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'dateDebut' => $dateDebut->format('Y-m-d'),
            'dateFin' => $dateFin->format('Y-m-d'),
            'sellerId' => $sellerId,
        ]);

        $rows = $result->fetchAllAssociative();

        // Initialisation
        $labels = [];
        $datasets = [];

        foreach ($rows as $row) {
            $mois = $row['mois'];
            $annee = $row['annee'];
            $label = \DateTime::createFromFormat('!m', $mois)->format('F') . ' ' . $annee;
            if (!in_array($label, $labels)) {
                $labels[] = $label;
            }

            $category = $row['name_category'];
            if (!isset($datasets[$category])) {
                $datasets[$category] = array_fill(0, count($labels), 0);
            }

            $index = array_search($label, $labels);
            $datasets[$category][$index] = (float)$row['nombre_vendus'];
        }

        // S'assurer que toutes les catégories ont des valeurs pour chaque mois
        foreach ($datasets as $cat => &$data) {
            $data += array_fill(count($data), count($labels) - count($data), 0);
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets
        ];
    }

    /**
     * Obtient les ventes par article pour une catégorie et un vendeur spécifique
     *
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @param int $sellerId
     * @param int $categoryId
     * @return array
     */
    public function getVentesParArticlePourCategorie(\DateTime $dateDebut, \DateTime $dateFin, int $sellerId, int $categoryId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                TO_CHAR(s.sale_date, 'YYYY') AS annee,
                TO_CHAR(s.sale_date, 'MM') AS mois,
                i.name_item AS article,
                SUM(cd.quantity) AS nombre_vendus
            FROM sale s
            INNER JOIN commande c ON s.id_commande = c.id_commande
            INNER JOIN commande_details cd ON cd.id_commande = c.id_commande
            INNER JOIN item_size isz ON cd.id_item_size = isz.id_item_size
            INNER JOIN item i ON i.id_item = isz.id_item
            INNER JOIN category cat ON i.id_category = cat.id_category
            INNER JOIN users seller ON i.id_seller = seller.id_user
            WHERE s.sale_date BETWEEN :dateDebut AND :dateFin
            AND s.is_paid = true
            AND seller.id_user = :sellerId
            AND cat.id_category = :categoryId
            GROUP BY annee, mois, i.name_item
            ORDER BY annee, mois, i.name_item
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'dateDebut' => $dateDebut->format('Y-m-d'),
            'dateFin' => $dateFin->format('Y-m-d'),
            'sellerId' => $sellerId,
            'categoryId' => $categoryId
        ]);

        $rows = $result->fetchAllAssociative();

        // Initialisation
        $labels = [];
        $datasets = [];

        foreach ($rows as $row) {
            $mois = $row['mois'];
            $annee = $row['annee'];
            $label = \DateTime::createFromFormat('!m', $mois)->format('F') . ' ' . $annee;

            if (!in_array($label, $labels)) {
                $labels[] = $label;
            }

            $article = $row['article'];
            if (!isset($datasets[$article])) {
                $datasets[$article] = array_fill(0, count($labels), 0);
            }

            $index = array_search($label, $labels);
            $datasets[$article][$index] = (int) $row['nombre_vendus'];
        }

        // S'assurer que toutes les séries ont des valeurs pour tous les mois
        foreach ($datasets as $cat => &$data) {
            // Forcer à un tableau indexé
            $data = array_values($data);

            // Compléter si nécessaire
            $manquants = count($labels) - count($data);
            if ($manquants > 0) {
                $data = array_merge($data, array_fill(0, $manquants, 0));
            }
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets
        ];
    }

    /**
     * Obtient les meilleurs articles d'un vendeur pour une période
     *
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @param int $sellerId
     * @param int $limit
     * @return array
     */
    public function getTopArticlesVendeur(\DateTime $dateDebut, \DateTime $dateFin, int $sellerId, int $limit): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                i.name_item AS name,
                i.images AS image_id,
                cat.name_category AS category,
                SUM(cd.quantity) AS sales,
                SUM(cd.quantity * cd.price) AS total_revenue,
                ROUND(AVG(cd.price), 2) AS average_price
            FROM sale s
            INNER JOIN commande c ON s.id_commande = c.id_commande
            INNER JOIN commande_details cd ON cd.id_commande = c.id_commande
            INNER JOIN item_size isz ON cd.id_item_size = isz.id_item_size
            INNER JOIN item i ON i.id_item = isz.id_item
            INNER JOIN category cat ON i.id_category = cat.id_category
            INNER JOIN users seller ON i.id_seller = seller.id_user
            WHERE s.sale_date BETWEEN :dateDebut AND :dateFin
            AND s.is_paid = true
            AND seller.id_user = :sellerId
            GROUP BY i.id_item, i.name_item, i.images, cat.name_category
            ORDER BY sales DESC
            LIMIT :limit
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'dateDebut' => $dateDebut->format('Y-m-d'),
            'dateFin' => $dateFin->format('Y-m-d'),
            'sellerId' => $sellerId,
            'limit' => $limit,
        ]);

        return $result->fetchAllAssociative();
    }

    /**
     * Obtient les meilleurs articles d'un vendeur
     *
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @param int $sellerId
     * @param int $limit
     * @return array
     */
    public function getTopArticles(int $sellerId, int $limit): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                i.name_item AS name,
                i.images AS image_id,
                cat.name_category AS category,
                SUM(cd.quantity) AS sales,
                SUM(cd.quantity * cd.price) AS total_revenue,
                ROUND(SUM(cd.quantity * cd.price) / NULLIF(SUM(cd.quantity), 0), 2) AS average_price
            FROM sale s
            INNER JOIN commande c ON s.id_commande = c.id_commande
            INNER JOIN commande_details cd ON cd.id_commande = c.id_commande
            INNER JOIN item_size isz ON cd.id_item_size = isz.id_item_size
            INNER JOIN item i ON i.id_item = isz.id_item
            INNER JOIN category cat ON i.id_category = cat.id_category
            INNER JOIN users seller ON i.id_seller = seller.id_user
            WHERE s.is_paid = true
            AND seller.id_user = :sellerId
            GROUP BY i.id_item, i.name_item, i.images, cat.name_category
            ORDER BY sales DESC
            LIMIT :limit
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'sellerId' => $sellerId,
            'limit' => $limit,
        ]);

        return $result->fetchAllAssociative();
    }

    /**
     * Obtient les meilleurs clients d'un vendeur
     *
     * @param int $sellerId
     * @param int $limit
     * @return array
     */
    public function getTopClients(int $sellerId, int $limit): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                u.id_user AS client_id,
                u.username AS client_name,
                u.email,
                u.contact,
                u.address,
                COUNT(cd.id_commande_detail) AS total_purchases,
                SUM(cd.price * cd.quantity) AS total_spent
            FROM commande c
            INNER JOIN sale s ON s.id_commande = c.id_commande
            INNER JOIN commande_details cd ON cd.id_commande = c.id_commande
            INNER JOIN users u ON c.id_client = u.id_user
            WHERE c.id_seller = :sellerId
            AND s.is_paid = true
            GROUP BY u.id_user, u.username, u.email, u.contact, u.address
            ORDER BY total_spent DESC
            LIMIT :limit
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'sellerId' => $sellerId,
            'limit' => $limit,
        ]);

        return $result->fetchAllAssociative();
    }

    /**
     * Convertit le numéro de mois en nom de mois en français
     *
     * @param int $mois
     * @return string
     */
    private function getMoisNom(int $mois): string
    {
        $moisNoms = [
            1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Août',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'
        ];

        return $moisNoms[$mois] ?? 'Inconnu';
    }
}
