<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ItemsStockRepository;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

class StockController extends AbstractController
{
    public function __construct(
        private ItemsStockRepository $itemsStockRepository,
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
            5,
            [
                'pageParameterName' => 'stock_page',
                'sortFieldParameterName' => 'stock_sort',
                'sortDirectionParameterName' => 'stock_direction'
            ]
        );

        return $this->render('admin/stock.html.twig', [
            'user'          => $user,
            'stockMovements'=> $stockMovements,
            'currentStock'  => $currentStock,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
            'dateD'         => $startDateValue,
            'dateF'         => $endDateValue,
        ]);
    }
}
