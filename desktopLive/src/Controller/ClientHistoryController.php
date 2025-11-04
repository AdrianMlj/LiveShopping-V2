<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SaleRepository as HistoryRepository;
use App\Repository\StateCommandeRepository;
use App\Entity\Sale;
use Dompdf\Dompdf;
use Dompdf\Options;

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

    #[Route('/client/history/download-invoice', name: 'app_client_history_download_invoice', methods: ['GET'])]
    public function downloadInvoice(Request $request): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');
        if (!$user) {
            return new Response('Non connecté', 401);
        }

        $saleId = (int)$request->query->get('sale_id');
        if (!$saleId) {
            return new Response('ID de vente manquant', 400);
        }

        $sale = $this->historyRepo->getSaleDetailsByIdForClient($saleId, $user->getId());
        if (!$sale) {
            return new Response('Vente introuvable', 404);
        }

        // Calculer le total
        $totalHT = 0;
        foreach ($sale->getCommande()->getDetails() as $detail) {
            $totalHT += $detail->getQuantity() * $detail->getPrice();
        }
        $tva = $totalHT * 0.2;
        $totalTTC = $totalHT + $tva;

        // Vérifier si GD est disponible
        $gdAvailable = extension_loaded('gd');
        
        // Préparer les données avec images en base64 (seulement si GD est disponible)
        $projectDir = $this->getParameter('kernel.project_dir');
        $itemsWithImages = [];
        
        foreach ($sale->getCommande()->getDetails() as $detail) {
            $item = $detail->getItemSize()->getItem();
            $imageBase64 = null;
            
            // Ne charger les images que si GD est disponible
            if ($gdAvailable && $item->getImages()) {
                $imagePath = $projectDir . '/public/Uploads/' . $item->getImages();
                if (file_exists($imagePath) && is_file($imagePath)) {
                    $imageData = file_get_contents($imagePath);
                    if ($imageData) {
                        // Déterminer le type MIME à partir de l'extension
                        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                        $mimeTypes = [
                            'jpg' => 'image/jpeg',
                            'jpeg' => 'image/jpeg',
                            'png' => 'image/png',
                            'gif' => 'image/gif',
                            'webp' => 'image/webp',
                            'svg' => 'image/svg+xml',
                        ];
                        $mimeType = $mimeTypes[$extension] ?? 'image/jpeg';
                        $imageBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                    }
                }
            }
            
            $itemsWithImages[] = [
                'item' => $item,
                'detail' => $detail,
                'imageBase64' => $imageBase64,
            ];
        }

        // Générer le HTML pour le PDF
        $html = $this->renderView('client/invoice_pdf.html.twig', [
            'sale' => $sale,
            'totalHT' => $totalHT,
            'tva' => $tva,
            'totalTTC' => $totalTTC,
            'invoiceNumber' => $saleId,
            'invoiceDate' => new \DateTime(),
            'orderDate' => $sale->getCommande()->getCreatedAt() ?? new \DateTime(),
            'itemsWithImages' => $itemsWithImages,
            'gdAvailable' => $gdAvailable,
        ]);

        // Si GD n'est pas disponible, supprimer toutes les balises img du HTML
        if (!$gdAvailable) {
            $html = preg_replace('/<img[^>]*>/i', '', $html);
            $html = preg_replace('/<img[^>]*\/>/i', '', $html);
        }

        // Configuration DomPDF
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', $projectDir);
        $options->set('enableCssFloat', true);
        $options->set('isPhpEnabled', false);

        try {
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Générer le nom du fichier
            $filename = 'Facture_' . $saleId . '_' . date('Y') . '.pdf';

            // Retourner le PDF
            return new Response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'Pragma' => 'public',
            ]);
        } catch (\Exception $e) {
            error_log('Erreur génération PDF: ' . $e->getMessage());
            return new Response('Erreur lors de la génération du PDF: ' . $e->getMessage(), 500);
        }
    }
}
