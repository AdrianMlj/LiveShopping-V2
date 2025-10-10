<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ItemsStockRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\ExportTemp;
use App\Repository\ExportTempRepository;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/stock', name: 'api_stock_')]
class StockControllerApi extends AbstractController
{
    public function __construct(
        private ItemsStockRepository $itemsStockRepository,
        private ExportTempRepository $exportTempRepository
    ) {}

    /**
     * Get stock movements by period and seller
     * GET /api/stock/movements?user_id=1&dateD=YYYY-MM-DD&dateF=YYYY-MM-DD&page=1&limit=10
     */
    #[Route('/movements', name: 'movements', methods: ['GET'])]
    public function getStockMovements(Request $request): JsonResponse
    {
        $sellerId = $request->query->get('user_id');

        if (!$sellerId) {
            return $this->json([
                'success' => false,
                'message' => 'user_id parameter is required'
            ], 400);
        }

        // Get date parameters
        $startDateParam = $request->query->get('dateD');
        $endDateParam = $request->query->get('dateF');
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        // Default dates
        $startDateValue = $startDateParam ?: (new \DateTime('first day of this month'))->format('Y-m-d');
        $endDateValue = $endDateParam ?: (new \DateTime('last day of this month'))->format('Y-m-d');

        try {
            $startDate = new \DateTime($startDateValue);
            $endDate = new \DateTime($endDateValue);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid date format'
            ], 400);
        }

        // Get movements (already returns array)
        $movements = $this->itemsStockRepository->findStockMovementByPeriodAndSeller(
            $startDate,
            $endDate,
            $sellerId
        );

        // Manual pagination
        $offset = ($page - 1) * $limit;
        $paginatedMovements = array_slice($movements, $offset, $limit);
        $totalItems = count($movements);
        $totalPages = ceil($totalItems / $limit);

        // Format movements data
        $formattedMovements = array_map(function($movement) {
            // Calculer la quantité (in_item - out_item) et déterminer le type
            $inItem = (int)($movement['in_item'] ?? 0);
            $outItem = (int)($movement['out_item'] ?? 0);
            $quantity = $inItem > 0 ? $inItem : -$outItem;
            $movementType = $inItem > 0 ? 'IN' : 'OUT';

            return [
                'id' => $movement['id_item_stock'] ?? null,
                'item_name' => $movement['name_item'] ?? null,
                'size_value' => $movement['value_size'] ?? null,
                'color_name' => $movement['name_color'] ?? null,
                'quantity' => abs($quantity),
                'movement_type' => $movementType,
                'movement_date' => $movement['date_move'] ?? null,
                'in_item' => $inItem,
                'out_item' => $outItem,
                '_debug_keys' => array_keys($movement) // Debug temporaire
            ];
        }, $paginatedMovements);

        return $this->json([
            'success' => true,
            'data' => $formattedMovements,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'items_per_page' => $limit
            ],
            'filters' => [
                'start_date' => $startDateValue,
                'end_date' => $endDateValue
            ]
        ]);
    }

    /**
     * Get current stock for seller
     * GET /api/stock/current?user_id=1&page=1&limit=10
     */
    #[Route('/current', name: 'current', methods: ['GET'])]
    public function getCurrentStock(Request $request): JsonResponse
    {
        $sellerId = $request->query->get('user_id');

        if (!$sellerId) {
            return $this->json([
                'success' => false,
                'message' => 'user_id parameter is required'
            ], 400);
        }
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        // Get stock data (already returns array)
        $stock = $this->itemsStockRepository->findCurrentStockBySeller($sellerId);

        // Manual pagination
        $offset = ($page - 1) * $limit;
        $paginatedStock = array_slice($stock, $offset, $limit);
        $totalItems = count($stock);
        $totalPages = ceil($totalItems / $limit);

        // Format stock data
        $formattedStock = array_map(function($item) {
            return [
                'item_id' => $item['itemid'] ?? null,
                'item_name' => $item['itemname'] ?? null,
                'item_size_id' => $item['itemsizeid'] ?? null,
                'size_value' => $item['valuesize'] ?? null,
                'color_name' => $item['colorname'] ?? null,
                'item_size_color_id' => $item['itemsizecolorid'] ?? null,
                'current_stock' => $item['currentstock'] ?? 0
            ];
        }, $paginatedStock);

        return $this->json([
            'success' => true,
            'data' => $formattedStock,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'items_per_page' => $limit
            ]
        ]);
    }

    /**
     * Get export data (pending export requests)
     * GET /api/stock/export?user_id=1&page=1&limit=10
     */
    #[Route('/export', name: 'export_data', methods: ['GET'])]
    public function getExportData(Request $request): JsonResponse
    {
        $sellerId = $request->query->get('user_id');

        if (!$sellerId) {
            return $this->json([
                'success' => false,
                'message' => 'user_id parameter is required'
            ], 400);
        }
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        // Get export data
        $exportDataArray = $this->exportTempRepository->getFullExportData($sellerId);

        // Manual pagination
        $offset = ($page - 1) * $limit;
        $paginatedExport = array_slice($exportDataArray, $offset, $limit);
        $totalItems = count($exportDataArray);
        $totalPages = ceil($totalItems / $limit);

        return $this->json([
            'success' => true,
            'data' => $paginatedExport,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'items_per_page' => $limit
            ]
        ]);
    }

    /**
     * Import CSV files
     * POST /api/stock/import
     * Expected: multipart/form-data with user_id, file1, file2, file3, file4
     */
    #[Route('/import', name: 'import', methods: ['POST'])]
    public function importCsv(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $sellerId = $request->request->get('user_id');

        if (!$sellerId) {
            return $this->json([
                'success' => false,
                'message' => 'user_id parameter is required'
            ], 400);
        }

        // Retrieve uploaded files
        $files = [
            $request->files->get('file1'),
            $request->files->get('file2'),
            $request->files->get('file3'),
            $request->files->get('file4'),
        ];

        // Check if at least one file was uploaded
        $hasFiles = false;
        foreach ($files as $file) {
            if ($file !== null) {
                $hasFiles = true;
                break;
            }
        }

        if (!$hasFiles) {
            return $this->json([
                'success' => false,
                'message' => 'No files uploaded'
            ], 400);
        }

        try {
            $importResult = $this->itemsStockRepository->importCsv($sellerId, $files, $em);

            if ($importResult === "true") {
                return $this->json([
                    'success' => true,
                    'message' => 'Import successful ✅'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => $importResult
                ], 400);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Import error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save export requests
     * POST /api/stock/export/save
     * Expected JSON: { "user_id": 1, "demandes": [...] }
     */
    #[Route('/export/save', name: 'export_save', methods: ['POST'])]
    public function saveExportRequests(Request $request, ExportTempRepository $exportRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $sellerId = $data['user_id'] ?? null;

        if (!$sellerId) {
            return $this->json([
                'success' => false,
                'message' => 'user_id parameter is required'
            ], 400);
        }

        $demandes = $data['demandes'] ?? null;

        if (!$demandes || !is_array($demandes)) {
            return $this->json([
                'success' => false,
                'message' => 'No export requests received or invalid format'
            ], 400);
        }

        try {
            $exportRepo->saveDemandes($demandes);
            return $this->json([
                'success' => true,
                'message' => 'Export requests saved successfully!'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error saving export requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a stock export request
     * DELETE /api/stock/export/{id}
     */
    #[Route('/export/{id}', name: 'export_delete', methods: ['DELETE'])]
    public function deleteExportRequest(int $id, EntityManagerInterface $em): JsonResponse
    {
        $export = $em->getRepository(ExportTemp::class)->find($id);

        if (!$export) {
            return $this->json([
                'success' => false,
                'message' => 'Export request not found'
            ], 404);
        }

        try {
            $em->remove($export);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Export request deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error deleting export request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate and download CSV files
     * POST /api/stock/export/generate
     * Expected JSON: { "user_id": 1, "demandes": [...] }
     */
    #[Route('/export/generate', name: 'export_generate', methods: ['POST'])]
    public function generateCsv(Request $request, ExportTempRepository $exportRepo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $sellerId = $data['user_id'] ?? null;

        if (!$sellerId) {
            return $this->json([
                'success' => false,
                'message' => 'user_id parameter is required'
            ], 400);
        }

        $demandes = $data['demandes'] ?? null;

        if (!$demandes || !is_array($demandes)) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid or missing demandes parameter'
            ], 400);
        }

        try {
            // Generate CSV data (includes color.csv)
            [$categoriesCsv, $itemsCsv, $sizesCsv, $colorCsv] = $exportRepo->buildCsvData($demandes);

            // Create export directory with timestamp
            $timestamp = date('Ymd_His');
            $exportDir = $this->getParameter('kernel.project_dir') . '/public/export_' . $timestamp . '/';

            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0777, true);
            }

            // Save CSV files
            $exportRepo->arrayToCsv($categoriesCsv, $exportDir . 'categories.csv');
            $exportRepo->arrayToCsv($itemsCsv, $exportDir . 'items.csv');
            $exportRepo->arrayToCsv($sizesCsv, $exportDir . 'sizes.csv');
            $exportRepo->arrayToCsv($colorCsv, $exportDir . 'color.csv');

            // Delete exported records from export_temp
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
                'success' => true,
                'message' => 'CSV files generated successfully and export_temp cleaned ✅',
                'folder' => '/export_' . $timestamp,
                'files' => [
                    '/export_' . $timestamp . '/categories.csv',
                    '/export_' . $timestamp . '/items.csv',
                    '/export_' . $timestamp . '/sizes.csv',
                    '/export_' . $timestamp . '/color.csv'
                ],
                'download_urls' => [
                    'categories' => $request->getSchemeAndHttpHost() . '/export_' . $timestamp . '/categories.csv',
                    'items' => $request->getSchemeAndHttpHost() . '/export_' . $timestamp . '/items.csv',
                    'sizes' => $request->getSchemeAndHttpHost() . '/export_' . $timestamp . '/sizes.csv',
                    'colors' => $request->getSchemeAndHttpHost() . '/export_' . $timestamp . '/color.csv'
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error generating CSV files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all stock data (movements, current stock, export data)
     * GET /api/stock/all?user_id=1&dateD=YYYY-MM-DD&dateF=YYYY-MM-DD&movements_page=1&stock_page=1&export_page=1
     */
    #[Route('/all', name: 'all', methods: ['GET'])]
    public function getAllStockData(Request $request): JsonResponse
    {
        $sellerId = $request->query->get('user_id');

        if (!$sellerId) {
            return $this->json([
                'success' => false,
                'message' => 'user_id parameter is required'
            ], 400);
        }

        // Get parameters
        $startDateParam = $request->query->get('dateD');
        $endDateParam = $request->query->get('dateF');
        $movementsPage = $request->query->getInt('movements_page', 1);
        $stockPage = $request->query->getInt('stock_page', 1);
        $exportPage = $request->query->getInt('export_page', 1);
        $limit = $request->query->getInt('limit', 10);

        // Default dates
        $startDateValue = $startDateParam ?: (new \DateTime('first day of this month'))->format('Y-m-d');
        $endDateValue = $endDateParam ?: (new \DateTime('last day of this month'))->format('Y-m-d');

        try {
            $startDate = new \DateTime($startDateValue);
            $endDate = new \DateTime($endDateValue);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid date format'
            ], 400);
        }

        // Get movements (already returns array)
        $movements = $this->itemsStockRepository->findStockMovementByPeriodAndSeller(
            $startDate,
            $endDate,
            $sellerId
        );
        $movementsOffset = ($movementsPage - 1) * $limit;
        $paginatedMovements = array_slice($movements, $movementsOffset, $limit);

        // Get current stock (already returns array)
        $stock = $this->itemsStockRepository->findCurrentStockBySeller($sellerId);
        $stockOffset = ($stockPage - 1) * $limit;
        $paginatedStock = array_slice($stock, $stockOffset, $limit);

        // Get export data
        $exportDataArray = $this->exportTempRepository->getFullExportData($sellerId);
        $exportOffset = ($exportPage - 1) * $limit;
        $paginatedExport = array_slice($exportDataArray, $exportOffset, $limit);

        return $this->json([
            'success' => true,
            'movements' => [
                'data' => array_map(function($m) {
                    $inItem = (int)($m['in_item'] ?? 0);
                    $outItem = (int)($m['out_item'] ?? 0);
                    $quantity = $inItem > 0 ? $inItem : -$outItem;
                    $movementType = $inItem > 0 ? 'IN' : 'OUT';

                    return [
                        'id' => $m['id_item_stock'] ?? null,
                        'item_name' => $m['name_item'] ?? null,
                        'size_value' => $m['value_size'] ?? null,
                        'color_name' => $m['name_color'] ?? null,
                        'quantity' => abs($quantity),
                        'movement_type' => $movementType,
                        'movement_date' => $m['date_move'] ?? null,
                        'in_item' => $inItem,
                        'out_item' => $outItem
                    ];
                }, $paginatedMovements),
                'pagination' => [
                    'current_page' => $movementsPage,
                    'total_pages' => ceil(count($movements) / $limit),
                    'total_items' => count($movements)
                ]
            ],
            'current_stock' => [
                'data' => array_map(function($s) {
                    return [
                        'item_id' => $s['itemid'] ?? null,
                        'item_name' => $s['itemname'] ?? null,
                        'item_size_id' => $s['itemsizeid'] ?? null,
                        'size_value' => $s['valuesize'] ?? null,
                        'color_name' => $s['colorname'] ?? null,
                        'item_size_color_id' => $s['itemsizecolorid'] ?? null,
                        'current_stock' => $s['currentstock'] ?? 0
                    ];
                }, $paginatedStock),
                'pagination' => [
                    'current_page' => $stockPage,
                    'total_pages' => ceil(count($stock) / $limit),
                    'total_items' => count($stock)
                ]
            ],
            'export_data' => [
                'data' => $paginatedExport,
                'pagination' => [
                    'current_page' => $exportPage,
                    'total_pages' => ceil(count($exportDataArray) / $limit),
                    'total_items' => count($exportDataArray)
                ]
            ],
            'filters' => [
                'start_date' => $startDateValue,
                'end_date' => $endDateValue
            ]
        ]);
    }
}

