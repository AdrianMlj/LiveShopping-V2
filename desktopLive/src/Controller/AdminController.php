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
use App\Entity\Item;
use App\Entity\Category;
use App\Entity\PriceItems;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        $defaultStart = new \DateTime('first day of this month');
        $defaultEnd = new \DateTime('last day of this month');
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

    #[Route('/api/categories', name: 'api_categories', methods: ['GET'])]
    public function getCategories(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();
        $data = array_map(function ($category) {
            return [
                'id' => $category->getId(),
                'name' => $category->getNameCategory(),
                'description' => $category->getDescription()
            ];
        }, $categories);

        return $this->json([
            'success' => true,
            'categories' => $data
        ]);
    }

    #[Route('/api/items/create', name: 'api_item_create', methods: ['POST'])]
    public function createItem(
        Request $request,
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        UsersRepository $usersRepository
    ): JsonResponse {
        $session = $request->getSession();
        $user = $session->get('user');

        if (!$user || !$user instanceof \App\Entity\Users) {
            return $this->json(['success' => false, 'message' => 'Utilisateur non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['success' => false, 'message' => 'Données JSON invalides'], 400);
        }

        // Validation des champs requis
        $required = ['name', 'category_id', 'price'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->json(['success' => false, 'message' => "Champ manquant : $field"], 400);
            }
        }

        try {
            // Récupérer la catégorie
            $category = $categoryRepository->find($data['category_id']);
            if (!$category) {
                return $this->json(['success' => false, 'message' => 'Catégorie introuvable'], 404);
            }

            // Créer le nouvel item
            $item = new Item();
            $item->setNameItem($data['name']);
            $item->setSeller($user);
            $item->setCategory($category);
            $item->setDescription($data['description'] ?? null);
            $item->setImages($data['images'] ?? null);

            $em->persist($item);
            $em->flush();

            // Créer le prix initial
            $priceItem = new PriceItems();
            $priceItem->setItem($item);
            $priceItem->setPrice((float)$data['price']);
            $priceItem->setDatePrice(new \DateTime());

            $em->persist($priceItem);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Item créé avec succès',
                'item' => [
                    'id' => $item->getId(),
                    'name' => $item->getNameItem(),
                    'category' => $category->getNameCategory(),
                    'price' => $priceItem->getPrice(),
                    'description' => $item->getDescription(),
                    'images' => $item->getImages()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/items/upload-image', name: 'api_item_upload_image', methods: ['POST'])]
    public function uploadItemImage(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('image');

        if (!$uploadedFile instanceof UploadedFile) {
            return $this->json(['success' => false, 'message' => 'Aucun fichier image fourni'], 400);
        }

        // Validation du type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($uploadedFile->getMimeType(), $allowedTypes)) {
            return $this->json(['success' => false, 'message' => 'Type de fichier non autorisé'], 400);
        }

        // Validation de la taille (max 5MB)
        if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
            return $this->json(['success' => false, 'message' => 'Fichier trop volumineux (max 5MB)'], 400);
        }

        try {
            // Générer un nom de fichier unique
            $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $uploadedFile->guessExtension();
            $fileName = $originalName . '_' . uniqid() . '.' . $extension;

            // Déplacer le fichier vers le dossier uploads
            $uploadedFile->move($this->getParameter('kernel.project_dir') . '/public/uploads', $fileName);

            return $this->json([
                'success' => true,
                'message' => 'Image uploadée avec succès',
                'filename' => $fileName
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload : ' . $e->getMessage()
            ], 500);
        }
    }
}
