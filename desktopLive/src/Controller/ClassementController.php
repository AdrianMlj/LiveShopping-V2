<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UsersRepository;
use App\Repository\SaleRepository;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

class ClassementController extends AbstractController
{
    public function __construct(
        private SaleRepository $saleRepository,
        private UsersRepository $usersRepository,
        private PaginatorInterface $paginator
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
        $allArticles = $this->saleRepository->getTopArticles($sellerId, 5); // null = pas de limite
        $allClients = $this->saleRepository->getTopClients($sellerId, 5);   // null = pas de limite

        // Calculs globaux basés sur TOUTES les données
        $globalStats = $this->calculateGlobalStats($allArticles, $allClients);

        // Paginer les articles
        $articlesPerPage = $request->query->getInt('articles_page', 1);
        $paginatedArticles = $this->paginator->paginate(
            $allArticles,
            $articlesPerPage,
            5, // 2 articles par page
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
            5, // 5 clients par page
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
