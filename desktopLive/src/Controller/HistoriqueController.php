<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\HistoryRepository;
use App\Repository\StateCommandeRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class HistoriqueController extends AbstractController
{
    public function __construct(
        private HistoryRepository $historyRepo,
        private StateCommandeRepository $stateRepo,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/history', name: 'app_history')]
    public function index(Request $request, HistoryRepository $historyRepo, StateCommandeRepository $stateRepo): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');

        if (!$user) {
            return $this->redirectToRoute('app_connection');
        }

        $sellerId = $user->getId();

        $filters = [
            'date_start' => $request->query->get('date_start') ? new \DateTime($request->query->get('date_start')) : null,
            'date_end'   => $request->query->get('date_end') ? new \DateTime($request->query->get('date_end')) : null,
            'state' => $request->query->get('state') !== null && is_numeric($request->query->get('state'))
                        ? (int)$request->query->get('state')
                        : null,
            'is_paid' => $request->query->get('is_paid') !== null && $request->query->get('is_paid') !== ''
                        ? (bool)$request->query->get('is_paid')
                        : null,
        ];

        $sales = $historyRepo->getSalesBySeller($sellerId, $filters);
        $states = $stateRepo->findAll();

        return $this->render('admin/historique.html.twig', [
            'sales' => $sales,
            'states' => $states,
        ]);
    }

    #[Route('/historyDetails', name: 'app_history_details')]
    public function details(Request $request, HistoryRepository $historyRepo): JsonResponse
    {
        $saleId = $request->query->get('sale_id');

        if (!$saleId) {
            return new JsonResponse(['error' => 'ID de vente manquant'], 400);
        }

        $sale = $historyRepo->getSaleDetailsById($saleId);

        if (!$sale) {
            return new JsonResponse(['error' => 'Vente introuvable'], 404);
        }

        // Render du template en string HTML
        $html = $this->renderView('admin/historyDetails.html.twig', [
            'sale' => $sale
        ]);

        return new JsonResponse(['html' => $html]);
    }
}
