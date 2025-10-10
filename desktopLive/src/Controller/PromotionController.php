<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\ItemRepository;
use App\Repository\CategoryRepository;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Category;
use App\Entity\PriceItems;
use App\Entity\Item;
use App\Entity\Users;

class PromotionController extends AbstractController
{
    public function __construct(
        private PaginatorInterface $paginator,
        private ItemRepository $itemRepository,
        private CategoryRepository $categoryRepository,
        private UsersRepository $usersRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/promotion', name: 'app_promotion', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');
        if (!$user) {
            return $this->redirectToRoute('app_connection');
        }

        $categoryId = $request->query->get('category_id');
        $categoryId = is_numeric($categoryId) ? (int)$categoryId : null;

        $rows = $this->itemRepository->findItemsWithAvailableSizesGrouped($user->getId(), $categoryId);
        $categories = $this->categoryRepository->findAll();

        return $this->render('admin/promotion.html.twig', [
            'rows' => $rows,
            'category_id' => $categoryId,
            'categories' => $categories,
        ]);
    }

    #[Route('/promotion/category/create', name: 'app_promotion_category_create', methods: ['POST'])]
    public function createCategory(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = trim($data['name'] ?? '');
        $description = $data['description'] ?? null;

        if ($name === '') {
            return $this->json([
                'success' => false,
                'message' => 'Le nom de la catégorie est requis.'
            ], 400);
        }

        $existing = $this->categoryRepository->findOneBy(['nameCategory' => $name]);
        if ($existing) {
            return $this->json([
                'success' => false,
                'message' => 'Une catégorie avec ce nom existe déjà.'
            ], 409);
        }

        $category = new Category();
        $category->setNameCategory($name);
        $category->setDescription($description);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Catégorie créée avec succès.',
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getNameCategory(),
                'description' => $category->getDescription(),
            ]
        ]);
    }

    #[Route('/promotion/item/update', name: 'app_promotion_item_update', methods: ['POST'])]
    public function updateItem(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $itemId = (int)($data['item_id'] ?? 0);
        $name = isset($data['name']) ? trim($data['name']) : null;
        $price = $data['price'] ?? null;
        $hasDescriptionKey = array_key_exists('description', $data);
        $description = $hasDescriptionKey ? (isset($data['description']) ? trim((string)$data['description']) : null) : null;

        if ($itemId <= 0 || $name === null || $name === '' || $price === null || !is_numeric($price)) {
            return $this->json([
                'success' => false,
                'message' => 'Paramètres invalides.'
            ], 400);
        }

        /** @var Item|null $item */
        $item = $this->entityManager->getRepository(Item::class)->find($itemId);
        if (!$item) {
            return $this->json([
                'success' => false,
                'message' => 'Article introuvable.'
            ], 404);
        }

        // Mettre à jour le nom
        $item->setNameItem($name);

        // Mettre à jour la description (si fournie)
        if ($hasDescriptionKey) {
            $item->setDescription($description !== '' ? $description : null);
        }

        // Ajouter un nouvel enregistrement de prix (historisé)
        $priceEntity = new PriceItems();
        $priceEntity->setItem($item);
        $priceEntity->setPrice((string)$price);
        $priceEntity->setDatePrice(new \DateTime());

        $this->entityManager->persist($priceEntity);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Article mis à jour avec succès.',
        ]);
    }

    #[Route('/promotion/item/create', name: 'app_promotion_item_create', methods: ['POST'])]
    public function createItem(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $sessionUser = $session->get('user');

        if (!$sessionUser) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non connecté.'
            ], 401);
        }

        // Récupérer l'utilisateur depuis la base de données
        $user = $this->usersRepository->find($sessionUser->getId());
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur introuvable.'
            ], 401);
        }

        try {
            $data = json_decode($request->getContent(), true);

            $name = trim($data['name'] ?? '');
            $categoryId = (int)($data['category_id'] ?? 0);
            $price = $data['price'] ?? null;
            $description = isset($data['description']) ? trim($data['description']) : null;

            if ($name === '' || $categoryId <= 0 || $price === null || !is_numeric($price)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Paramètres invalides.'
                ], 400);
            }

            $category = $this->categoryRepository->find($categoryId);
            if (!$category) {
                return $this->json([
                    'success' => false,
                    'message' => 'Catégorie introuvable.'
                ], 404);
            }

            $item = new Item();
            $item->setNameItem($name);
            $item->setSeller($user);
            $item->setCategory($category);
            $item->setDescription($description);

            $this->entityManager->persist($item);
            $this->entityManager->flush();

            // Créer le prix initial
            $priceEntity = new PriceItems();
            $priceEntity->setItem($item);
            $priceEntity->setPrice((string)$price);
            $priceEntity->setDatePrice(new \DateTime());

            $this->entityManager->persist($priceEntity);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Article créé avec succès.',
                'item' => [
                    'id' => $item->getId(),
                    'name' => $item->getNameItem(),
                    'category' => $category->getNameCategory(),
                    'price' => $price,
                    'description' => $item->getDescription()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
}
