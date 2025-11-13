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
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\ItemsStock;
use App\Entity\ItemSizeColor;

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
            3,
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
            'exportDataFull'=> $exportDataArray,
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
            $request->files->get('file4'),
        ];

        $importResult = $this->itemsStockRepository->importCsv($sellerId, $files, $em);

        // Vérifie si succès ou erreur
        if ($importResult == "true") {
            $message = "Import réussi ✅";
        } else {
            $message = $importResult;
        }

        return new Response($message);
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

    #[Route('/delete_stock/{id}', name: 'delete_stock', methods: ['DELETE'])]
    public function deleteStock(int $id, EntityManagerInterface $em): JsonResponse
    {
        $export = $em->getRepository(ExportTemp::class)->find($id);

        if (!$export) {
            return new JsonResponse(['success' => false, 'message' => 'Article introuvable'], 404);
        }

        $em->remove($export);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/export_csv', name: 'app_export_csv', methods: ['POST'])]
    public function createCSV(Request $request, ExportTempRepository $exportRepo, EntityManagerInterface $em): Response
    {
        $demandesJson = $request->request->get('demandes');
        if (!$demandesJson) {
            return $this->json([
                'status' => 'error',
                'message' => 'Paramètre "demandes" manquant.'
            ], 400);
        }

        $demandes = json_decode($demandesJson, true);
        if (!$demandes || !is_array($demandes)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Format JSON invalide.'
            ], 400);
        }

        try {
            // Générer les données CSV (ajout color.csv)
            [$categoriesCsv, $itemsCsv, $sizesCsv, $colorCsv] = $exportRepo->buildCsvData($demandes);

            // Timestamp pour le dossier
            $timestamp = date('Ymd_His');
            $exportDir = $this->getParameter('kernel.project_dir') . '/public/export_' . $timestamp . '/';
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0777, true);
            }

            // Sauvegarder les CSV
            $exportRepo->arrayToCsv($categoriesCsv, $exportDir . 'categories.csv');
            $exportRepo->arrayToCsv($itemsCsv, $exportDir . 'items.csv');
            $exportRepo->arrayToCsv($sizesCsv, $exportDir . 'sizes.csv');
            $exportRepo->arrayToCsv($colorCsv, $exportDir . 'color.csv');

            // 🔹 Supprimer les enregistrements exportés
            foreach ($demandes as $demande) {
                if (!empty($demande['id'])) {
                    $entity = $exportRepo->find($demande['id']);
                    if ($entity) {
                        $em->remove($entity);
                    }
                }
            }
            $em->flush();

            return $this->json([
                'status'  => 'success',
                'message' => '3 CSV générés avec succès et export_temp nettoyé ✅',
                'folder'  => '/export_' . $timestamp,
                'files'   => [
                    '/export_' . $timestamp . '/categories.csv',
                    '/export_' . $timestamp . '/items.csv',
                    '/export_' . $timestamp . '/sizes.csv',
                    '/export_' . $timestamp . '/color.csv'
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status'  => 'error',
                'message' => 'Erreur lors de la génération des CSV : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/stock/in', name: 'app_stock_in', methods: ['POST'])]
    public function stockIn(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true) ?? [];
            $idItemSizeColor = (int)($data['idItemSize'] ?? 0);
            $qty = (int)($data['qty'] ?? 0);

            if ($idItemSizeColor <= 0 || $qty <= 0) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Paramètres invalides'
                ], 400);
            }

            $itemSizeColor = $em->getRepository(ItemSizeColor::class)->find($idItemSizeColor);
            if (!$itemSizeColor) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Variation introuvable'
                ], 404);
            }

            $movement = new ItemsStock();
            $movement->setItemSizeColor($itemSizeColor);
            $movement->setInItem($qty);
            $movement->setOutItem(0);
            $movement->setDateMove(new \DateTime());

            $em->persist($movement);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Stock ajouté'
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur serveur',
            ], 500);
        }
    }

}
