<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UsersRepository;
use App\Repository\SaleRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\GoalsRepository;
use App\Repository\ItemsStockRepository;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ClassementController extends AbstractController
{
    public function __construct(
        private SaleRepository $saleRepository,
        private UsersRepository $usersRepository,
        private GoalsRepository $goalsRepository,
        private ItemsStockRepository $itemsStockRepository,
        private PaginatorInterface $paginator,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/classement', name: 'app_classement')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $defaultSeller = $session->get('user');

        if (!$defaultSeller || !$defaultSeller instanceof \App\Entity\Users) {
            throw $this->createNotFoundException('Aucun utilisateur connecté trouvé dans la session');
        }

        $sellerId = $defaultSeller->getId();

        // Récupérer TOUTES les données pour les calculs globaux
        $allArticles = $this->saleRepository->getTopArticles($sellerId, 100); // null = pas de limite
        $allClients = $this->saleRepository->getTopClients($sellerId, 100);   // null = pas de limite

        // Calculs globaux basés sur TOUTES les données
        $globalStats = $this->calculateGlobalStats($allArticles, $allClients);

        // Paginer les articles
        $articlesPerPage = $request->query->getInt('articles_page', 1);
        $paginatedArticles = $this->paginator->paginate(
            $allArticles,
            $articlesPerPage,
            3, // 2 articles par page
            [
                'pageParameterName' => 'articles_page',
                'sortFieldParameterName' => 'articles_sort',
                'sortDirectionParameterName' => 'articles_direction'
            ]
        );

        // Paginer les clients
        $clientsPerPage = $request->query->getInt('clients_page', 1);
        $paginatedClients = $this->paginator->paginate(
            $allClients,
            $clientsPerPage,
            3, // 5 clients par page
            [
                'pageParameterName' => 'clients_page',
                'sortFieldParameterName' => 'clients_sort',
                'sortDirectionParameterName' => 'clients_direction'
            ]
        );

        return $this->render('admin/classement.html.twig', [
            'topArticles' => $paginatedArticles,
            'topClients' => $paginatedClients,
            'globalStats' => $globalStats, // Passer les statistiques globales
        ]);
    }

    #[Route('/objectif-mensuel', name: 'app_objectif_mensuel')]
    public function objectifMensuel(Request $request): Response
    {
        $session = $request->getSession();
        $defaultSeller = $session->get('user');

        if (!$defaultSeller || !$defaultSeller instanceof \App\Entity\Users) {
            throw $this->createNotFoundException('Aucun utilisateur connecté trouvé dans la session');
        }

        $sellerId = $defaultSeller->getId();

        // Dates depuis formulaire ou par défaut
        $startDate = $request->request->get('dateD')
            ? new \DateTime($request->request->get('dateD'))
            : new \DateTime('first day of this month');
        $endDate = $request->request->get('dateF')
            ? new \DateTime($request->request->get('dateF'))
            : new \DateTime('last day of this month');

        // 1) Réalisé (sans Goals) – CA et ventes par mois
        $conn = $this->entityManager->getConnection();
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
            'dateDebut' => $startDate->format('Y-m-d'),
            'dateFin' => $endDate->format('Y-m-d'),
        ])->fetchAllAssociative();

        // 2) Capacité stock globale (pour donner une borne haute de CA)
        $capacity = $this->itemsStockRepository->getSellerStockCapacity($sellerId);

        // 3) Construire la structure attendue par le template, en utilisant la projection temporelle
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

            if ($dateMois->format('Y-m') < $moisCourant) {
                $projection_ca = $ca_realise;
                $projection_ventes = $ventes_realisees;
            } elseif ($dateMois->format('Y-m') === $moisCourant) {
                $jours_passes = (int)$today->format('d');
                $projection_ca = $jours_passes > 0 ? ($ca_realise / $jours_passes) * $jours_total : 0;
                $projection_ventes = $jours_passes > 0 ? ($ventes_realisees / $jours_passes) * $jours_total : 0;
                // Borne par la capacité stock CA
                if ($capacity['capacity_ca'] > 0) {
                    $projection_ca = min($projection_ca, $capacity['capacity_ca']);
                }
            } else {
                $projection_ca = 0;
                $projection_ventes = 0;
            }

            $monthlyGoals[] = [
                'annee' => $annee,
                'mois' => $mois,
                'target_ca' => $capacity['capacity_ca'], // cible = capacité stock CA
                'target_ventes' => (int)$capacity['capacity_units'],
                'ca_realise' => $ca_realise,
                'ventes_realisees' => $ventes_realisees,
                'projection_ca' => $projection_ca,
                'projection_ventes' => (int)round($projection_ventes),
                'ecart_ca' => $ca_realise - $capacity['capacity_ca'],
                'ecart_ventes' => $ventes_realisees - (int)$capacity['capacity_units'],
            ];
        }

        $today = new \DateTime();
        /// PROJECTION = (realise jsq'a maintenant / j ecoule) * nbr total de j du mois ///
        foreach ($monthlyGoals as &$goal) {
            $annee = $goal['annee'] ?? date('Y');
            $mois = str_pad($goal['mois'], 2, '0', STR_PAD_LEFT); // ex: "09"
            $dateMois = \DateTime::createFromFormat('Y-m-d', "$annee-$mois-01");
            $jours_total = (int) $dateMois->format('t');

            $moisCourant = $today->format('Y-m');

            if ($dateMois->format('Y-m') < $moisCourant) {
                // mois passé → projection = réalisé
                $goal['projection_ca'] = $goal['ca_realise'];
                $goal['projection_ventes'] = $goal['ventes_realisees'];
            } elseif ($dateMois->format('Y-m') === $moisCourant) {
                // mois en cours → projection basée sur jours écoulés
                $jours_passes = (int) $today->format('d');
                $goal['projection_ca'] = $jours_passes > 0
                    ? ($goal['ca_realise'] / $jours_passes) * $jours_total
                    : 0;
                $goal['projection_ventes'] = $jours_passes > 0
                    ? ($goal['ventes_realisees'] / $jours_passes) * $jours_total
                    : 0;
            } else {
                // mois futur → projection = 0
                $goal['projection_ca'] = 0;
                $goal['projection_ventes'] = 0;
            }
        }

        return $this->render('admin/objectifMensuel.html.twig', [
            'monthlyGoals' => $monthlyGoals,
            'dateD' => $startDate->format('Y-m-d'),
            'dateF' => $endDate->format('Y-m-d'),
        ]);
    }

    private function calculateGlobalStats(array $allArticles, array $allClients): array
    {
        // Calculs pour les articles
        $totalRevenue = 0;
        $totalSales = 0;
        foreach ($allArticles as $article) {
            $totalRevenue += $article['total_revenue'] ?? 0;
            $totalSales += $article['sales'] ?? 0;
        }

        // Calculs pour les clients
        $totalClientSpending = 0;
        $totalClientPurchases = 0;
        foreach ($allClients as $client) {
            $totalClientSpending += $client['total_spent'] ?? 0;
            $totalClientPurchases += $client['total_purchases'] ?? 0;
        }

        // Calculs des moyennes
        $avgRevenuePerArticle = count($allArticles) > 0 ? round($totalRevenue / count($allArticles), 0) : 0;
        $avgSalesPerArticle = count($allArticles) > 0 ? round($totalSales / count($allArticles), 0) : 0;
        $avgSpendingPerClient = count($allClients) > 0 ? round($totalClientSpending / count($allClients), 0) : 0;
        $avgPurchasesPerClient = count($allClients) > 0 ? round($totalClientPurchases / count($allClients), 1) : 0;

        // Parts de marché
        $topArticleShare = $totalSales > 0 && count($allArticles) > 0
            ? round(($allArticles[0]['sales'] ?? 0) / $totalSales * 100, 1)
            : 0;
        $topClientShare = $totalClientSpending > 0 && count($allClients) > 0
            ? round(($allClients[0]['total_spent'] ?? 0) / $totalClientSpending * 100, 1)
            : 0;

        return [
            'totalRevenue' => $totalRevenue,
            'totalSales' => $totalSales,
            'totalClientSpending' => $totalClientSpending,
            'totalClientPurchases' => $totalClientPurchases,
            'avgRevenuePerArticle' => $avgRevenuePerArticle,
            'avgSalesPerArticle' => $avgSalesPerArticle,
            'avgSpendingPerClient' => $avgSpendingPerClient,
            'avgPurchasesPerClient' => $avgPurchasesPerClient,
            'topArticleShare' => $topArticleShare,
            'topClientShare' => $topClientShare,
            'totalArticlesCount' => count($allArticles),
            'totalClientsCount' => count($allClients),
        ];
    }
}
