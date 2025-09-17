<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ItemsStockRepository;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\ExportTemp;
use App\Entity\ItemSize;
use App\Repository\ExportTempRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockController extends AbstractController
{
    public function __construct(
        private ItemsStockRepository $itemsStockRepository,
        private ExportTempRepository $exportTempRepository,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/stock', name: 'app_stock')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');

        if (!$user) {
            return $this->redirectToRoute('app_connection');
        }

        $sellerId = $user->getId();

        // 📌 Gestion des filtres date (POST → Session → GET → valeurs par défaut)
        $startDateParam = $request->request->get('dateD') ?: $request->query->get('dateD');
        $endDateParam   = $request->request->get('dateF') ?: $request->query->get('dateF');

        if ($request->isMethod('POST')) {
            $session->set('stock_dateD', $startDateParam);
            $session->set('stock_dateF', $endDateParam);
        }

        $startDateValue = $startDateParam
            ?: $session->get('stock_dateD')
            ?: (new \DateTime('first day of this month'))->format('Y-m-d');

        $endDateValue = $endDateParam
            ?: $session->get('stock_dateF')
            ?: (new \DateTime('last day of this month'))->format('Y-m-d');

        $startDate = new \DateTime($startDateValue);
        $endDate   = new \DateTime($endDateValue);

        // 📌 Query mouvements filtrés
        $movementsQuery = $this->itemsStockRepository->findStockMovementByPeriodAndSeller(
            $startDate,
            $endDate,
            $sellerId
        );

        // Pagination mouvements
        $movementsPage = $request->query->getInt('movements_page', 1);
        $stockMovements = $this->paginator->paginate(
            $movementsQuery,
            $movementsPage,
            5,
            [
                'pageParameterName' => 'movements_page',
                'sortFieldParameterName' => 'movements_sort',
                'sortDirectionParameterName' => 'movements_direction'
            ]
        );

        // 📌 Query stock actuel
        $stockQuery = $this->itemsStockRepository->findCurrentStockBySeller($sellerId);

        // Pagination stock actuel
        $stockPage = $request->query->getInt('stock_page', 1);
        $currentStock = $this->paginator->paginate(
            $stockQuery,
            $stockPage,
            3,
            [
                'pageParameterName' => 'stock_page',
                'sortFieldParameterName' => 'stock_sort',
                'sortDirectionParameterName' => 'stock_direction'
            ]
        );

        // 📌 Export complet
        $exportDataArray = $this->exportTempRepository->getFullExportData($sellerId);

        $exportPage = $request->query->getInt('export_page', 1);
        $exportData = $this->paginator->paginate(
            $exportDataArray,
            $exportPage,
            3,
            [
                'pageParameterName' => 'export_page',
                'sortFieldParameterName' => 'export_sort',
                'sortDirectionParameterName' => 'export_direction'
            ]
        );

        return $this->render('admin/stock.html.twig', [
            'user'          => $user,
            'stockMovements'=> $stockMovements,
            'currentStock'  => $currentStock,
            'exportData'    => $exportData,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
            'dateD'         => $startDateValue,
            'dateF'         => $endDateValue,
        ]);
    }

    #[Route('/import', name: 'app_import', methods: ['POST'])]
    public function importDate(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');
        $sellerId = $user->getId();

        // Récupérer les fichiers uploadés
        $files = [
            $request->files->get('file1'),
            $request->files->get('file2'),
            $request->files->get('file3'),
        ];

        // Appel de la fonction importCsv
        $importResult = $this->itemsStockRepository->importCsv($sellerId, $files, $em);

        // Affiche le résultat directement dans la page
        return new Response('<pre>'.$importResult.'</pre>');
    }

    #[Route('/export', name: 'app_export', methods: ['POST'])]
    public function exportDate(Request $request, ExportTempRepository $exportRepo): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');
        $sellerId = $user->getId();

        $demandesJson = $request->request->get('demandes');
        $demandes = json_decode($demandesJson, true);

        if (!$demandes) {
            return new Response("Aucune demande reçue", 400);
        }

        try {
            $exportRepo->saveDemandes($demandes);
            return new Response("Demandes enregistrées avec succès !");
        } catch (\Exception $e) {
            return new Response("Erreur lors de l'enregistrement : " . $e->getMessage(), 500);
        }
    }
}
