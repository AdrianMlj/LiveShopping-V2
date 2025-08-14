<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use App\Repository\SaleRepository;
use App\Repository\UsersRepository;
use App\Entity\LiveDetails;
use App\Entity\Live;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
class AdminController extends AbstractController
{
    public function __construct(
        private SaleRepository $saleRepository,
        private UsersRepository $userRepository,
        private ItemRepository $itemRepository
    ) {}
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        Request $request,
        CategoryRepository $categoryRepository,
        UsersRepository $usersRepository,
        SaleRepository $saleRepository
    ): Response {
        // :épingle: Valeurs par défaut
        $defaultStart = new \DateTime('first day of January this year');
        $defaultEnd = new \DateTime('last day of December this year');
        // :épingle: Récupérer les valeurs du formulaire
        $dateD = $request->request->get('dateD') ? new \DateTime($request->request->get('dateD')) : $defaultStart;
        $dateF = $request->request->get('dateF') ? new \DateTime($request->request->get('dateF')) : $defaultEnd;
        $categoryId = $request->request->get('category');
        // :épingle: Vendeur par défaut
        $session = $request->getSession();
        $defaultSeller = $session->get('user');
        if (!$defaultSeller || !$defaultSeller instanceof \App\Entity\Users) {
            throw $this->createNotFoundException('Aucun utilisateur connecté trouvé dans la session');
        }
        $defaultSeller = $usersRepository->find($defaultSeller->getId());
        if($categoryId == null)
        {
            $categoryId = 1;
        }
        // :histogramme: Statistiques globales
        $stats = $saleRepository->getStatistiquesVendeur($dateD, $dateF, $defaultSeller->getId());
        $ventesParCategorieParMois = $saleRepository->getVentesVendeurParCategorieParMois(
            $dateD,
            $dateF,
            $defaultSeller->getId()
        );
        $ventesParArticle = $saleRepository->getVentesParArticlePourCategorie(
            $dateD,
            $dateF,
            $defaultSeller->getId(),
            $categoryId
        );
        $bestSeller = $saleRepository->getTopArticlesVendeur(
            $dateD,
            $dateF,
            $defaultSeller->getId(),
            3
        );
        return $this->render('admin/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'category' => $categoryRepository->find($categoryId),
            'stats' => $stats,
            'ventesParCategorieParMois' => $ventesParCategorieParMois,
            'ventesParArticle' => $ventesParArticle,
            'start' => $dateD->format('Y-m-d'),
            'end' => $dateF->format('Y-m-d'),
            'defaultSeller' => $defaultSeller,
            'bestSellers' => $bestSeller ?? null
        ]);
    }
    #[Route('/api/dashboard', name: 'api_dashboard', methods: ['POST'])]
    public function apiDashboard(
        Request $request,
        CategoryRepository $categoryRepository,
        UsersRepository $usersRepository,
        SaleRepository $saleRepository
    ): JsonResponse {
        // Valeurs par défaut
        $defaultStart = new \DateTime('first day of January this year');
        $defaultEnd = new \DateTime('last day of December this year');

        // Récupérer les paramètres GET
        $data = json_decode($request->getContent(), true);

        $dateD = isset($data['dateD']) ? new \DateTime($data['dateD']) : $defaultStart;
        $dateF = isset($data['dateF']) ? new \DateTime($data['dateF']) : $defaultEnd;
        $categoryId = $data['category'] ?? 1;

        $defaultSeller = $usersRepository->find($data['id_user']);
        // $defaultSeller = $usersRepository->find(1);


        // Statistiques et données
        $stats = $saleRepository->getStatistiquesVendeur($dateD, $dateF, $data['id_user']);
        $ventesParCategorieParMois = $saleRepository->getVentesVendeurParCategorieParMois($dateD, $dateF, $data['id_user']);
        $ventesParArticle = $saleRepository->getVentesParArticlePourCategorie($dateD, $dateF, $data['id_user'], $categoryId);
        $bestSeller = $saleRepository->getTopArticlesVendeur($dateD, $dateF, $data['id_user'], 3);

        // Optionnel : transformer objets Doctrine si besoin

        return new JsonResponse([
            'dates' => [
                'start' => $dateD->format('Y-m-d'),
                'end' => $dateF->format('Y-m-d')
            ],
            'stats' => $stats,
            'ventesParCategorieParMois' => $ventesParCategorieParMois,
            'ventesParArticle' => $ventesParArticle,
            'categories' => $categoryRepository->findAll(),
            'category' => $categoryRepository->find($categoryId),
            'seller' => [
                'id' => $defaultSeller->getId(),
                'username' => $defaultSeller->getUsername(),
            ],
            'bestSellers' => $bestSeller ?? null
        ]);
    }

    #[Route('/liveStart', name: 'admin_live_form')]
    public function startLiveSelect(
        Request $request,
        ItemRepository $itemRepository,
        UsersRepository $usersRepository
    ): Response {
        $session = $request->getSession();
        $user = $session->get('user');
        $userID = $usersRepository->find($user->getId());

        $items = $itemRepository->findAvailableItems($userID);
        return $this->render('admin/liveForm.html.twig', [
            'items' => $items
        ]);
    }

    #[Route('/liveConfirm', name: 'admin_live_confirm', methods: ['POST'])]
    public function confirmLive(
        Request $request,
        EntityManagerInterface $em,
        UsersRepository $usersRepository,
        ItemRepository $itemRepository
    ): Response {
        $session = $request->getSession();
        $user = $usersRepository->find($session->get('user')->getId());
        // Récupérer les données du formulaire
        $titre = $request->request->get('titre');
        $description = $request->request->get('description');
        $selectedItems = $request->request->all('items');
        if (!is_array($selectedItems)) {
            $selectedItems = [];
        }
        // Créer et remplir l'entité Live
        $live = new Live();
        $live->setStartLive(new \DateTime());
        $live->setSeller($user);
        $live->setTitre($titre);
        $live->setDescription($description);
        $em->persist($live);
        $em->flush();
        // Insérer dans LiveDetails chaque item choisi
        foreach ($selectedItems as $itemId) {
            $item = $itemRepository->find($itemId);
            if ($item) {
                $liveDetail = new LiveDetails();
                $liveDetail->setLive($live);
                $liveDetail->setItem($item);
                $em->persist($liveDetail);
            }
        }
        $em->flush();
        return $this->redirectToRoute('app_live', ['id' => $live->getId()]);
    }

    #[Route('/stopLive/{id}', name: 'admin_live_stop')]
    public function stopLive(Request $request, Live $live, EntityManagerInterface $em, UsersRepository $usersRepository): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');
        $userID = $usersRepository->find($user->getId());
        $user = $usersRepository->find($userID);
        if ($live->getSeller()->getId() !== $user->getId()) {
            throw $this->createNotFoundException('Vous ne pouvez pas arrêter ce live.');
        }
        $live->setEndLive(new \DateTime());
        $em->flush();
        $this->addFlash('success', 'Le live a été terminé avec succès.');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/enDirecte', name: 'app_live')]
    public function liveInterface(Request $request, EntityManagerInterface $em, UsersRepository $usersRepository): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');
        $userID = $usersRepository->find($user->getId());
        $user = $usersRepository->find($userID);
        $activeLive = $em->getRepository(Live::class)->findOneBy([
            'seller' => $user,
            'endLive' => null
        ]);
        return $this->render('admin/live.html.twig', [
            'live' => $activeLive,
            'seller' => $user,
            'titre' => $activeLive?->getTitre(),
            'description' => $activeLive?->getDescription(),
        ]);
    }
}
