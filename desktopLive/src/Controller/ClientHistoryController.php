<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\HistoryRepository;
use App\Repository\StateCommandeRepository;
use App\Entity\Sale;

class ClientHistoryController extends AbstractController
{
    public function __construct(
        private HistoryRepository $historyRepo,
        private StateCommandeRepository $stateRepo,
        private EntityManagerInterface $em
    ) {}

    #[Route('/client/history', name: 'app_client_history', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');
        if (!$user) {
            return $this->redirectToRoute('app_connection');
        }

        $clientId = $user->getId();
        $filters = [
            'date_start' => $request->query->get('date_start') ? new \DateTime($request->query->get('date_start')) : null,
            'date_end'   => $request->query->get('date_end') ? new \DateTime($request->query->get('date_end')) : null,
            'state'      => $request->query->get('state') !== null && is_numeric($request->query->get('state'))
                            ? (int)$request->query->get('state')
                            : null,
            'is_paid'    => $request->query->get('is_paid') !== null && $request->query->get('is_paid') !== ''
                            ? (bool)$request->query->get('is_paid')
                            : null,
        ];

        $sales = $this->historyRepo->getSalesByClient($clientId, $filters);
        $states = $this->stateRepo->findAll();

        return $this->render('client/history.html.twig', [
            'sales' => $sales,
            'states' => $states,
            'user' => $user,
        ]);
    }

    #[Route('/client/history/details', name: 'app_client_history_details', methods: ['GET'])]
    public function details(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $user = $session->get('user');
        if (!$user) {
            return new JsonResponse(['error' => 'Non connecté'], 401);
        }
        $saleId = (int)$request->query->get('sale_id');
        if (!$saleId) {
            return new JsonResponse(['error' => 'ID de vente manquant'], 400);
        }

        $sale = $this->historyRepo->getSaleDetailsByIdForClient($saleId, $user->getId());
        if (!$sale) {
            return new JsonResponse(['error' => 'Vente introuvable'], 404);
        }

        $html = $this->renderView('client/historyDetails.html.twig', [
            'sale' => $sale
        ]);

        return new JsonResponse(['html' => $html]);
    }

    #[Route('/client/history/mark-paid', name: 'app_client_history_mark_paid', methods: ['POST'])]
    public function markPaid(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $user = $session->get('user');
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Non connecté'], 401);
        }
        $saleId = (int)($request->request->get('sale_id') ?? 0);
        if (!$saleId) {
            return new JsonResponse(['success' => false, 'message' => 'ID manquant'], 400);
        }

        /** @var Sale|null $sale */
        $sale = $this->historyRepo->find($saleId);
        if (!$sale || !$sale->getCommande() || $sale->getCommande()->getClient()?->getId() !== $user->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Vente introuvable'], 404);
        }

        $sale->setIsPaid(true);
        $this->em->persist($sale);
        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\HistoryRepository;
use App\Repository\StateCommandeRepository;
use App\Repository\UsersRepository;

class ClientHistoryController extends AbstractController
{
    public function __construct(
        private HistoryRepository $historyRepo,
        private StateCommandeRepository $stateRepo,
        private EntityManagerInterface $em,
        private UsersRepository $usersRepo,
    ) {}

    #[Route('/client/history', name: 'app_client_history')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $userSession = $session->get('user');
        if (!$userSession) {
            return $this->redirectToRoute('app_connection');
        }

        $client = $this->usersRepo->find($userSession->getId());
        if (!$client) {
            return $this->redirectToRoute('app_connection');
        }

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

        $sales = $this->historyRepo->getSalesByClient($client->getId(), $filters);
        $states = $this->stateRepo->findAll();

        return $this->render('client/history.html.twig', [
            'sales' => $sales,
            'states' => $states,
        ]);
    }

    #[Route('/client/history/details', name: 'app_client_history_details')]
    public function details(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $userSession = $session->get('user');
        if (!$userSession) {
            return new JsonResponse(['error' => 'Non connecté'], 401);
        }
        $clientId = $userSession->getId();
        $saleId = (int) $request->query->get('sale_id');
        if (!$saleId) {
            return new JsonResponse(['error' => 'ID de vente manquant'], 400);
        }

        $sale = $this->historyRepo->getSaleDetailsByIdForClient($saleId, $clientId);
        if (!$sale) {
            return new JsonResponse(['error' => 'Vente introuvable'], 404);
        }

        $html = $this->renderView('client/historyDetails.html.twig', [
            'sale' => $sale
        ]);
        return new JsonResponse(['html' => $html]);
    }

    #[Route('/client/history/mark-paid', name: 'app_client_history_mark_paid', methods: ['POST'])]
    public function markPaid(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $userSession = $session->get('user');
        if (!$userSession) {
            return new JsonResponse(['error' => 'Non connecté'], 401);
        }
        $clientId = $userSession->getId();
        $saleId = (int) ($request->request->get('sale_id') ?? 0);
        if (!$saleId) {
            return new JsonResponse(['error' => 'ID de vente manquant'], 400);
        }

        $sale = $this->historyRepo->getSaleDetailsByIdForClient($saleId, $clientId);
        if (!$sale) {
            return new JsonResponse(['error' => 'Vente introuvable'], 404);
        }

        $sale->setIsPaid(true);
        $this->em->persist($sale);
        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }
}
