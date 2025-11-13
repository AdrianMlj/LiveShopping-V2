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
use App\Entity\ItemSizeColor;
use App\Entity\ItemSize;
use App\Entity\ItemsStock;
use App\Entity\Size;
use App\Entity\Color;
use App\Service\CloudinaryService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    #[Route('/promotion/item/{id}/colors', name: 'app_promotion_item_colors', methods: ['GET'])]
    public function getItemColors(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Item::class)->find($id);

        if (!$item) {
            return $this->json([
                'success' => false,
                'message' => 'Article introuvable.'
            ], 404);
        }

        $colors = [];

        // Parcourir les tailles pour récupérer les couleurs
        foreach ($item->getItemSizes() as $itemSize) {
            foreach ($itemSize->getItemSizeColors() as $itemSizeColor) {
                $colorId = $itemSizeColor->getColor()->getId();

                // Éviter les doublons
                if (!isset($colors[$colorId])) {
                    $colors[$colorId] = [
                        'id' => $colorId,
                        'name' => $itemSizeColor->getColor()->getNameColor(),
                        'item_size_color_id' => $itemSizeColor->getId(),
                        'image' => $itemSizeColor->getImages()
                    ];
                }
            }
        }

        return $this->json([
            'success' => true,
            'colors' => array_values($colors)
        ]);
    }

    #[Route('/promotion/color/{id}/upload-image', name: 'app_promotion_color_upload_image', methods: ['POST'])]
    public function uploadColorImage(int $id, Request $request, CloudinaryService $cloudinaryService): JsonResponse
    {
        $itemSizeColor = $this->entityManager->getRepository(ItemSizeColor::class)->find($id);

        if (!$itemSizeColor) {
            return $this->json([
                'success' => false,
                'message' => 'Variante de couleur introuvable.'
            ], 404);
        }

        /** @var UploadedFile|null $imageFile */
        $imageFile = $request->files->get('image');

        if (!$imageFile) {
            return $this->json([
                'success' => false,
                'message' => 'Aucune image fournie.'
            ], 400);
        }

        try {
            // Supprimer l'ancienne image si elle existe sur Cloudinary
            $oldImage = $itemSizeColor->getImages();
            if ($oldImage && str_starts_with($oldImage, 'http') && str_contains($oldImage, 'cloudinary.com')) {
                try {
                    $cloudinaryService->deleteImage($oldImage);
                } catch (\Exception $e) {
                    // Continuer même si la suppression échoue
                }
            }

            // Upload vers Cloudinary
            $imageUrl = $cloudinaryService->uploadImageResized(
                $imageFile,
                'items/variants',
                800,
                800
            );

            // Mettre à jour toutes les variantes de cette couleur pour cet article
            $item = $itemSizeColor->getItemSize()->getItem();
            foreach ($item->getItemSizes() as $itemSize) {
                foreach ($itemSize->getItemSizeColors() as $isc) {
                    if ($isc->getColor()->getId() === $itemSizeColor->getColor()->getId()) {
                        $isc->setImages($imageUrl);
                    }
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Image de la variante uploadée avec succès.',
                'image_url' => $imageUrl,
                'color_name' => $itemSizeColor->getColor()->getNameColor()
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/promotion/sizes', name: 'app_promotion_sizes', methods: ['GET'])]
    public function getSizes(): JsonResponse
    {
        try {
            $sizes = $this->entityManager->getRepository(Size::class)->findAll();
            $data = array_map(function (Size $size) {
                return [
                    'id' => $size->getId(),
                    'value' => $size->getNameSize(),
                    'name' => $size->getNameSize()
                ];
            }, $sizes);

            return $this->json([
                'success' => true,
                'sizes' => $data
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tailles : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/promotion/colors', name: 'app_promotion_colors', methods: ['GET'])]
    public function getColors(): JsonResponse
    {
        try {
            $colors = $this->entityManager->getRepository(Color::class)->findAll();
            $data = array_map(function (Color $color) {
                return [
                    'id' => $color->getId(),
                    'name' => $color->getNameColor()
                ];
            }, $colors);

            return $this->json([
                'success' => true,
                'colors' => $data
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des couleurs : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/promotion/item/{id}/sizes', name: 'app_promotion_item_sizes', methods: ['POST'])]
    public function addItemSizes(int $id, Request $request): JsonResponse
    {
        try {
            $item = $this->entityManager->getRepository(Item::class)->find($id);
            if (!$item) {
                return $this->json([
                    'success' => false,
                    'message' => 'Article introuvable.'
                ], 404);
            }

            $data = json_decode($request->getContent(), true);
            $sizeIds = $data['sizes'] ?? [];

            if (empty($sizeIds)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Aucune taille fournie.'
                ], 400);
            }

            $created = 0;
            foreach ($sizeIds as $sizeId) {
                // Vérifier si la combinaison existe déjà
                $existing = $this->entityManager->getRepository(ItemSize::class)->findOneBy([
                    'item' => $item,
                    'size' => $this->entityManager->getRepository(Size::class)->find($sizeId)
                ]);

                if (!$existing) {
                    $size = $this->entityManager->getRepository(Size::class)->find($sizeId);
                    if ($size) {
                        $itemSize = new ItemSize();
                        $itemSize->setItem($item);
                        $itemSize->setSize($size);
                        $itemSize->setValueSize($size->getNameSize());
                        $this->entityManager->persist($itemSize);
                        $created++;
                    }
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => "$created taille(s) associée(s) à l'article.",
                'created' => $created
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/promotion/item/{id}/colors', name: 'app_promotion_item_colors_post', methods: ['POST'])]
    public function addItemColors(int $id, Request $request): JsonResponse
    {
        try {
            $item = $this->entityManager->getRepository(Item::class)->find($id);
            if (!$item) {
                return $this->json([
                    'success' => false,
                    'message' => 'Article introuvable.'
                ], 404);
            }

            $data = json_decode($request->getContent(), true);
            $colorIds = $data['colors'] ?? [];

            if (empty($colorIds)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Aucune couleur fournie.'
                ], 400);
            }

            // Récupérer toutes les tailles de l'article
            $itemSizes = $item->getItemSizes();
            if ($itemSizes->isEmpty()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Veuillez d\'abord ajouter des tailles à l\'article.'
                ], 400);
            }

            $created = 0;
            foreach ($itemSizes as $itemSize) {
                foreach ($colorIds as $colorId) {
                    // Vérifier si la combinaison existe déjà
                    $existing = $this->entityManager->getRepository(ItemSizeColor::class)->findOneBy([
                        'itemSize' => $itemSize,
                        'color' => $this->entityManager->getRepository(Color::class)->find($colorId)
                    ]);

                    if (!$existing) {
                        $color = $this->entityManager->getRepository(Color::class)->find($colorId);
                        if ($color) {
                            $itemSizeColor = new ItemSizeColor();
                            $itemSizeColor->setItemSize($itemSize);
                            $itemSizeColor->setColor($color);
                            $this->entityManager->persist($itemSizeColor);
                            $created++;
                        }
                    }
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => "$created variante(s) couleur créée(s).",
                'created' => $created
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/promotion/item/{id}/color/{colorId}/image', name: 'app_promotion_item_color_image', methods: ['POST'])]
    public function uploadItemColorImage(int $id, int $colorId, Request $request, CloudinaryService $cloudinaryService): JsonResponse
    {
        try {
            $item = $this->entityManager->getRepository(Item::class)->find($id);
            if (!$item) {
                return $this->json([
                    'success' => false,
                    'message' => 'Article introuvable.'
                ], 404);
            }

            $color = $this->entityManager->getRepository(Color::class)->find($colorId);
            if (!$color) {
                return $this->json([
                    'success' => false,
                    'message' => 'Couleur introuvable.'
                ], 404);
            }

            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('image');
            if (!$imageFile) {
                return $this->json([
                    'success' => false,
                    'message' => 'Aucune image fournie.'
                ], 400);
            }

            // Upload vers Cloudinary
            $imageUrl = $cloudinaryService->uploadImageResized(
                $imageFile,
                'items/variants',
                800,
                800
            );

            // Mettre à jour toutes les variantes de cette couleur pour cet article
            $updated = 0;
            foreach ($item->getItemSizes() as $itemSize) {
                foreach ($itemSize->getItemSizeColors() as $itemSizeColor) {
                    if ($itemSizeColor->getColor()->getId() === $colorId) {
                        $itemSizeColor->setImages($imageUrl);
                        $updated++;
                    }
                }
            }

            if ($updated === 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'Aucune variante trouvée pour cette couleur. Créez d\'abord les tailles et couleurs.'
                ], 400);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Image uploadée avec succès.',
                'image_url' => $imageUrl,
                'updated' => $updated
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/promotion/item/{id}/stocks', name: 'app_promotion_item_stocks', methods: ['POST'])]
    public function addItemStocks(int $id, Request $request): JsonResponse
    {
        try {
            $item = $this->entityManager->getRepository(Item::class)->find($id);
            if (!$item) {
                return $this->json([
                    'success' => false,
                    'message' => 'Article introuvable.'
                ], 404);
            }

            $data = json_decode($request->getContent(), true);
            $stocks = $data['stocks'] ?? [];

            if (empty($stocks)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Aucun stock fourni.'
                ], 400);
            }

            $created = 0;
            foreach ($stocks as $stockData) {
                $sizeId = $stockData['size_id'] ?? null;
                $colorId = $stockData['color_id'] ?? null;
                $qty = $stockData['qty_available'] ?? 0;

                if (!$sizeId || !$colorId || $qty <= 0) {
                    continue;
                }

                // Trouver la combinaison ItemSizeColor
                $itemSize = null;
                foreach ($item->getItemSizes() as $is) {
                    if ($is->getSize()->getId() === $sizeId) {
                        $itemSize = $is;
                        break;
                    }
                }

                if (!$itemSize) {
                    continue;
                }

                $itemSizeColor = null;
                foreach ($itemSize->getItemSizeColors() as $isc) {
                    if ($isc->getColor()->getId() === $colorId) {
                        $itemSizeColor = $isc;
                        break;
                    }
                }

                if (!$itemSizeColor) {
                    continue;
                }

                // Vérifier si un stock existe déjà
                $existingStock = null;
                foreach ($itemSizeColor->getStocks() as $stock) {
                    $existingStock = $stock;
                    break;
                }

                if ($existingStock) {
                    // Mettre à jour le stock existant
                    $existingStock->setInItem($qty);
                    $existingStock->setOutItem(0);
                    $existingStock->setDateMove(new \DateTime());
                } else {
                    // Créer un nouveau stock
                    $itemsStock = new ItemsStock();
                    $itemsStock->setItemSizeColor($itemSizeColor);
                    $itemsStock->setInItem($qty);
                    $itemsStock->setOutItem(0);
                    $itemsStock->setDateMove(new \DateTime());
                    $this->entityManager->persist($itemsStock);
                }

                $created++;
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => "$created stock(s) enregistré(s).",
                'created' => $created
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }
}
