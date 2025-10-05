<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategoryRepository;
use App\Repository\SaleRepository;
use App\Repository\LiveRepository;
use App\Repository\UsersRepository;
use App\Repository\LiveDetailsRepository;
use App\Repository\FavoritesRepository;
use App\Repository\FavoriteDetailsRepository;
use App\Repository\ItemSizeRepository;
use App\Entity\Users;
use App\Entity\Favorites;
use App\Entity\FavoriteDetails;
use App\Entity\ItemSize;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ClientController extends AbstractController
{
    #[Route('/client/rate-product/{id}', name: 'app_client_rate_product', methods: ['POST'])]
    public function rateProduct(Request $request, $id): Response
    {
        $session = $request->getSession();
        $ratings = $session->get('ratings', []);
        $rating = (int)$request->request->get('rating', 0);
        if ($rating >= 1 && $rating <= 5) {
            $ratings[$id] = $rating;
            $session->set('ratings', $ratings);
        }
        return $this->redirectToRoute('app_home');
    }
    #[Route('/client/checkout', name: 'app_client_checkout')]
    public function checkout(Request $request): Response
    {
        // Ici, on peut afficher un message de confirmation ou récapitulatif
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $cartTotal = 0;
        foreach ($cart as $item) {
            $cartTotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
        }
        return $this->render('client/checkout.html.twig', [
            'cart' => $cart,
            'cartTotal' => $cartTotal
        ]);
    }
    #[Route('/client/remove-cart/{id}', name: 'app_client_remove_cart', methods: ['POST'])]
    public function removeCart(Request $request, $id): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $cart = array_filter($cart, function($item) use ($id) {
            return $item['id'] != $id;
        });
        $session->set('cart', array_values($cart));
        return $this->redirectToRoute('app_client_panier');
    }
    #[Route('/client/add-cart/{id}', name: 'app_client_add_cart', methods: ['POST'])]
    public function addCart(Request $request, $id, \App\Repository\ItemRepository $itemRepository, \App\Repository\PriceItemsRepository $priceItemsRepository): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $name = $request->request->get('name');
        $priceInput = $request->request->get('price');
        $images = $request->request->get('images');
        $quantityInput = $request->request->get('quantity', 1);

        $quantity = (int) $quantityInput;
        if ($quantity <= 0) {
            $quantity = 1;
        }

        $price = is_numeric($priceInput) ? (float) $priceInput : null;
        if ($price === null || $price <= 0) {
            $item = $itemRepository->find($id);
            if ($item) {
                $lastPrice = null;
                foreach ($item->getPriceItems() as $p) {
                    if ($lastPrice === null || $p->getDatePrice() > $lastPrice->getDatePrice()) {
                        $lastPrice = $p;
                    }
                }
                if ($lastPrice) {
                    $price = (float) $lastPrice->getPrice();
                } else {
                    // Tentative via repository si relation paresseuse indisponible
                    $prices = $priceItemsRepository->findBy(['item' => $item], ['datePrice' => 'DESC'], 1);
                    if ($prices && count($prices) > 0) {
                        $price = (float) $prices[0]->getPrice();
                    } else {
                        $price = 0.0;
                    }
                }
                if (!$name) {
                    $name = $item->getNameItem();
                }
                if (!$images && method_exists($item, 'getImages')) {
                    $images = $item->getImages();
                }
            } else {
                $price = 0.0;
            }
        }
        // Vérifie si le produit existe déjà dans le panier
        $found = false;
        foreach ($cart as &$item) {
            if ($item['id'] == $id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $selectedSizeId = $request->request->get('itemSizeId');
            $selectedColorId = $request->request->get('colorId');
            $cart[] = [
                'id' => $id,
                'name' => $name,
                'price' => $price,
                'images' => $images,
                'quantity' => $quantity,
                'itemSizeId' => $selectedSizeId ? (int)$selectedSizeId : null,
                'colorId' => $selectedColorId ? (int)$selectedColorId : null,
            ];
        }
        $session->set('cart', $cart);
        return $this->redirectToRoute('app_home');
    }
    #[Route('/client/panier', name: 'app_client_panier')]
    public function panier(Request $request, \App\Repository\ItemRepository $itemRepo, \App\Repository\ItemSizeRepository $itemSizeRepo): Response
    {
        // Exemple : récupération du panier depuis la session
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $cartTotal = 0;
        $userSession = $session->get('user');

        if (!$userSession) {
            return $this->redirectToRoute('app_connection');
        }
        foreach ($cart as $item) {
            $cartTotal += ((float)($item['price'] ?? 0)) * ((int)($item['quantity'] ?? 1));
        }

        // Build sizes and colors map per item for selectors
        $sizesByItem = [];
        $colorsBySize = [];
        $itemsCache = [];
        foreach ($cart as $ci) {
            $itemId = (int)($ci['id'] ?? 0);
            if ($itemId <= 0) { continue; }
            if (!isset($sizesByItem[$itemId])) {
                $sizes = $itemSizeRepo->findBy(['item' => $itemId]);
                $sizesByItem[$itemId] = array_map(function($sz){
                    return [
                        'id' => $sz->getId(),
                        'label' => trim(($sz->getValueSize() ?? '') . ($sz->getSize() ? (' (' . $sz->getSize()->getNameSize() . ')') : '')),
                    ];
                }, $sizes);
                // Colors per size (if any)
                foreach ($sizes as $sz) {
                    $sid = $sz->getId();
                    $colorsBySize[$sid] = [];
                    foreach ($sz->getItemSizeColors() as $isc) {
                        if ($isc->getColor()) {
                            $colorsBySize[$sid][] = [
                                'id' => $isc->getColor()->getId(),
                                'name' => $isc->getColor()->getNameColor() ?? ('#'.$isc->getColor()->getId()),
                            ];
                        }
                    }
                }
            }
        }

        return $this->render('client/panier.html.twig', [
            'cart' => $cart,
            'cartTotal' => $cartTotal,
            'sizesByItem' => $sizesByItem,
            'colorsBySize' => $colorsBySize,
        ]);
    }

    #[Route('/client/cart/update-size', name: 'app_client_update_size', methods: ['POST'])]
    public function updateCartSize(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $itemId = (int)$request->request->get('itemId');
        $sizeId = (int)$request->request->get('itemSizeId');
        if (!$itemId || !$sizeId) {
            return $this->json(['success' => false, 'message' => 'Paramètres invalides'], 400);
        }
        foreach ($cart as &$ci) {
            if ((int)$ci['id'] === $itemId) {
                $ci['itemSizeId'] = $sizeId;
                // Reset color when size changes
                $ci['colorId'] = null;
                break;
            }
        }
        $session->set('cart', $cart);
        return $this->json(['success' => true]);
    }

    #[Route('/client/cart/update-color', name: 'app_client_update_color', methods: ['POST'])]
    public function updateCartColor(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $itemId = (int)$request->request->get('itemId');
        $colorId = (int)$request->request->get('colorId');
        if (!$itemId || !$colorId) {
            return $this->json(['success' => false, 'message' => 'Paramètres invalides'], 400);
        }
        foreach ($cart as &$ci) {
            if ((int)$ci['id'] === $itemId) {
                $ci['colorId'] = $colorId;
                break;
            }
        }
        $session->set('cart', $cart);
        return $this->json(['success' => true]);
    }

    #[Route('/client/checkout/submit', name: 'app_client_checkout_submit', methods: ['POST'])]
    public function submitCheckout(
        Request $request,
        \Doctrine\ORM\EntityManagerInterface $em,
        \App\Repository\UsersRepository $usersRepo,
        \App\Repository\ItemRepository $itemRepo,
        \App\Repository\ItemSizeRepository $itemSizeRepo,
        \App\Repository\StateCommandeRepository $stateRepo
    ): JsonResponse {
        $session = $request->getSession();
        $userSession = $session->get('user');
        if (!$userSession) {
            return $this->json(['success' => false, 'message' => 'Non connecté'], 401);
        }
        $client = $usersRepo->find($userSession->getId());
        if (!$client) {
            return $this->json(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
        }

        $paymentMethod = (string)$request->request->get('paymentMethod', 'cod');
        $isPaid = in_array($paymentMethod, ['mvola', 'orange', 'card'], true);

        $cart = $session->get('cart', []);
        if (!$cart || count($cart) === 0) {
            return $this->json(['success' => false, 'message' => 'Panier vide'], 400);
        }

        // Group items by seller
        $groups = [];
        foreach ($cart as $ci) {
            $item = $itemRepo->find((int)$ci['id']);
            if (!$item) { continue; }
            $sellerId = $item->getSeller() ? $item->getSeller()->getId() : 0;
            if (!isset($groups[$sellerId])) { $groups[$sellerId] = []; }
            $groups[$sellerId][] = [$ci, $item];
        }

        // Resolve a default state for new orders (create one if missing)
        $pendingState = $stateRepo->findOneBy(['nameState' => 'En attente']);
        if (!$pendingState) {
            $pendingState = $stateRepo->findOneBy([]);
        }
        if (!$pendingState) {
            $pendingState = new \App\Entity\StateCommande();
            $pendingState->setNameState('En attente');
            $em->persist($pendingState);
            $em->flush();
        }

        try {
            foreach ($groups as $sellerId => $items) {
                // Resolve seller entity
                $seller = null;
                if ($sellerId) { $seller = $usersRepo->find($sellerId); }

                $commande = new \App\Entity\Commande();
                $commande->setState($pendingState);
                $commande->setClient($client);
                if ($seller) { 
                    $commande->setSeller($seller); 
                } else {
                    throw new \InvalidArgumentException('Vendeur introuvable pour un article du panier');
                }
                $commande->setCreatedAt(new \DateTime());

                // Details
                foreach ($items as [$ci, $item]) {
                    $detail = new \App\Entity\CommandeDetails();
                    // Choose size: selected or fallback first available
                    $itemSize = null;
                    $selectedSizeId = isset($ci['itemSizeId']) && $ci['itemSizeId'] ? (int)$ci['itemSizeId'] : null;
                    if ($selectedSizeId) {
                        $itemSize = $itemSizeRepo->find($selectedSizeId);
                    } else {
                        $sizes = $itemSizeRepo->findBy(['item' => $item->getId()]);
                        if ($sizes) { $itemSize = $sizes[0]; }
                    }
                    if (!$itemSize) {
                        throw new \InvalidArgumentException('Veuillez choisir une taille pour l\'article "'.$item->getNameItem().'"');
                    }
                    $detail->setItemSize($itemSize);
                    $detail->setQuantity((int)($ci['quantity'] ?? 1));
                    $detail->setPrice((string)number_format((float)($ci['price'] ?? 0), 2, '.', ''));
                    $commande->addDetail($detail);
                }

                $em->persist($commande);

                $sale = new \App\Entity\Sale();
                $sale->setCommande($commande);
                $sale->setSaleDate(new \DateTime());
                $sale->setIsPaid($isPaid);
                $em->persist($sale);
            }

            $em->flush();

            // Clear cart after success
            $session->set('cart', []);
            return $this->json(['success' => true, 'redirect' => $this->generateUrl('app_client_history')]);
        } catch (\Throwable $e) {
            $status = ($e instanceof \InvalidArgumentException) ? 400 : 500;
            return $this->json(['success' => false, 'message' => $e->getMessage()], $status);
        }
    }

    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(
        Request $request,
        \App\Repository\ItemRepository $itemRepo,
        \App\Repository\UsersRepository $usersRepo,
        \App\Repository\LiveRepository $liveRepo,
        \App\Repository\PriceItemsRepository $priceRepo,
        \App\Repository\FollowSellerRepository $followRepo
    ): Response {
        $session = $request->getSession();
        $userSession = $session->get('user');
        $currentUser = $userSession ? $usersRepo->find($userSession->getId()) : null;

        $q = trim((string)$request->query->get('q', ''));
        $sort = (string)$request->query->get('sort', 'name_asc');

        // Articles
        $items = $itemRepo->findAll();
        $articleResults = [];
        foreach ($items as $it) {
            $hay = strtolower(($it->getNameItem() ?? '') . ' ' . ($it->getCategory() ? $it->getCategory()->getNameCategory() : ''));
            if ($q === '' || str_contains($hay, strtolower($q))) {
                $last = null;
                foreach ($it->getPriceItems() as $p) {
                    if ($last === null || $p->getDatePrice() > $last->getDatePrice()) { $last = $p; }
                }
                $price = $last ? $last->getPrice() : null;
                $articleResults[] = [
                    'id' => $it->getId(),
                    'name' => $it->getNameItem(),
                    'image' => method_exists($it, 'getImages') ? $it->getImages() : null,
                    'price' => $price,
                ];
            }
        }

        // Vendeurs (suivables)
        $users = $usersRepo->findAll();
        $sellerResults = [];
        $followingIds = [];
        if ($currentUser) {
            $links = $followRepo->findBy(['client' => $currentUser]);
            foreach ($links as $lnk) { $followingIds[] = $lnk->getSeller()->getId(); }
        }
        foreach ($users as $u) {
            if (!$u->isSeller()) continue;
            $hay = strtolower(($u->getUsername() ?? '') . ' ' . ($u->getCountry() ?? ''));
            if ($q === '' || str_contains($hay, strtolower($q))) {
                $sellerResults[] = [
                    'id' => $u->getId(),
                    'username' => $u->getUsername(),
                    'images' => $u->getImages(),
                    'following' => in_array($u->getId(), $followingIds, true)
                ];
            }
        }

        // Lives
        $lives = $liveRepo->findAll();
        $liveResults = [];
        foreach ($lives as $lv) {
            $seller = $lv->getSeller();
            $hay = strtolower(($seller ? $seller->getUsername() : '') . ' live');
            if ($q === '' || str_contains($hay, strtolower($q))) {
                $liveResults[] = [
                    'id' => $lv->getId(),
                    'isLive' => $lv->getEndLive() === null,
                    'seller' => $seller ? [
                        'id' => $seller->getId(),
                        'username' => $seller->getUsername(),
                        'images' => $seller->getImages(),
                    ] : null
                ];
            }
        }

        // Tri simple par nom
        $cmpAsc = fn($a,$b)=> strcmp(($a['name'] ?? $a['username'] ?? ''), ($b['name'] ?? $b['username'] ?? ''));
        if ($sort === 'name_asc') {
            usort($articleResults, $cmpAsc);
            usort($sellerResults, $cmpAsc);
        } elseif ($sort === 'name_desc') {
            usort($articleResults, fn($a,$b)=> -$cmpAsc($a,$b));
            usort($sellerResults, fn($a,$b)=> -$cmpAsc($a,$b));
        }

        return $this->render('search/index.html.twig', [
            'user' => $currentUser,
            'query' => $q,
            'sort' => $sort,
            'articles' => $articleResults,
            'sellers' => $sellerResults,
            'lives' => $liveResults,
        ]);
    }

    #[Route('/search/suggest', name: 'app_search_suggest', methods: ['GET'])]
    public function searchSuggest(
        Request $request,
        \App\Repository\ItemRepository $itemRepo,
        \App\Repository\UsersRepository $usersRepo,
        \App\Repository\LiveRepository $liveRepo
    ): JsonResponse {
        $q = trim((string)$request->query->get('q', ''));
        if ($q === '') {
            return $this->json(['articles' => [], 'sellers' => [], 'lives' => []]);
        }
        $qLower = strtolower($q);

        $articles = [];
        foreach ($itemRepo->findAll() as $it) {
            $name = $it->getNameItem() ?? '';
            if (str_contains(strtolower($name), $qLower)) {
                $articles[] = ['id' => $it->getId(), 'name' => $name];
                if (count($articles) >= 5) break;
            }
        }

        $sellers = [];
        foreach ($usersRepo->findAll() as $u) {
            if (!$u->isSeller()) continue;
            $name = $u->getUsername() ?? '';
            if (str_contains(strtolower($name), $qLower)) {
                $sellers[] = ['id' => $u->getId(), 'username' => $name];
                if (count($sellers) >= 5) break;
            }
        }

        $lives = [];
        foreach ($liveRepo->findAll() as $lv) {
            $seller = $lv->getSeller();
            $label = $seller ? $seller->getUsername() : 'Live';
            if (str_contains(strtolower($label), $qLower)) {
                $lives[] = ['id' => $lv->getId(), 'label' => $label];
                if (count($lives) >= 5) break;
            }
        }

        return $this->json(['articles' => $articles, 'sellers' => $sellers, 'lives' => $lives]);
    }
    #[Route('/client/favorite/toggle-all-sizes/{itemId}', name: 'toggle_favorite_all_sizes', methods: ['POST'])]
    public function toggleFavoriteAllSizes(
        int $itemId,
        UsersRepository $usersRepository,
        FavoritesRepository $favRepo,
        FavoriteDetailsRepository $favDetailRepo,
        ItemSizeRepository $itemSizeRepo,
        Request $request
    ): JsonResponse
    {
        $session = $request->getSession();
        $userSession = $session->get('user');

        if (!$userSession) {
            return $this->json(['success' => false, 'message' => 'Non connecté'], 401);
        }

        $user = $usersRepository->find($userSession->getId());

        $itemSizes = $itemSizeRepo->findBy(['item' => $itemId]);
        if (!$itemSizes || count($itemSizes) === 0) {
            return $this->json(['success' => false, 'message' => 'Aucune taille trouvée'], 404);
        }

        // Cherche l'entrée Favorites existante pour ce client
        $favorite = $favRepo->findOneBy(['client' => $user]);
        if (!$favorite) {
            $favorite = new Favorites();
            $favorite->setClient($user);
            $favorite->setCreateAt(new \DateTime());
            $favRepo->save($favorite, true);
        }

        $added = 0;
        foreach ($itemSizes as $itemSize) {
            $exists = $favDetailRepo->findOneBy(['favorites' => $favorite, 'itemSize' => $itemSize]);
            if (!$exists) {
                $favDetail = new FavoriteDetails();
                $favDetail->setFavorites($favorite);
                $favDetail->setItemSize($itemSize);
                $favDetailRepo->save($favDetail, false);
                $added++;
            }
        }
        $favDetailRepo->getEntityManager()->flush();

        return $this->json(['success' => true, 'action' => 'added', 'count' => $added]);
    }
    
    #[Route('/client/favorite/remove-all-sizes/{itemId}', name: 'remove_favorite_all_sizes', methods: ['POST'])]
    public function removeFavoriteAllSizes(
        int $itemId,
        UsersRepository $usersRepository,
        FavoritesRepository $favRepo,
        FavoriteDetailsRepository $favDetailRepo,
        ItemSizeRepository $itemSizeRepo,
        Request $request,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): JsonResponse {
        $session = $request->getSession();
        $userSession = $session->get('user');
        if (!$userSession) {
            return $this->json(['success' => false, 'message' => 'Non connecté'], 401);
        }

        try {
            $user = $usersRepository->find($userSession->getId());
            if (!$user) {
                return $this->json(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
            }

            $favorite = $favRepo->findOneBy(['client' => $user]);
            if (!$favorite) {
                return $this->json(['success' => false, 'message' => 'Aucun favori pour cet utilisateur'], 404);
            }

            $itemSizes = $itemSizeRepo->findBy(['item' => $itemId]);
            if (!$itemSizes || count($itemSizes) === 0) {
                return $this->json(['success' => false, 'message' => 'Aucune taille trouvée pour cet article'], 404);
            }

            $removed = 0;
            foreach ($itemSizes as $itemSize) {
                $detail = $favDetailRepo->findOneBy(['favorites' => $favorite, 'itemSize' => $itemSize]);
                if ($detail) {
                    $entityManager->remove($detail);
                    $removed++;
                }
            }
            $entityManager->flush();

            return $this->json(['success' => true, 'action' => 'removed', 'count' => $removed]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Exception',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // ...existing code...

    #[Route('/client/remove-favorite-by-id/{favId}', name: 'remove_favorite_by_id', methods: ['POST'])]
    public function removeFavoriteById(
        int $favId,
        FavoriteDetailsRepository $favDetailRepo,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): JsonResponse {
        // favId = id du Favorites
        $favoriteDetails = $favDetailRepo->findBy(['favorites' => $favId]);
        $favoritesRepo = $entityManager->getRepository(\App\Entity\Favorites::class);
        $favorites = $favoritesRepo->find($favId);
        if ($favoriteDetails && count($favoriteDetails) > 0) {
            try {
                foreach ($favoriteDetails as $favDetail) {
                    $entityManager->remove($favDetail);
                }
                $entityManager->flush();
                // Vérifier s'il reste des FavoriteDetails pour ce favori
                $remainingDetails = $favDetailRepo->findBy(['favorites' => $favId]);
                if ($favorites && count($remainingDetails) === 0) {
                    $entityManager->remove($favorites);
                    $entityManager->flush();
                }
                return $this->json(['success' => true, 'action' => 'removed']);
            } catch (\Throwable $e) {
                return $this->json([
                    'success' => false,
                    'message' => 'Exception',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'favId' => $favId
                ], 500);
            }
        }
        return $this->json(['success' => false, 'message' => 'Favori non trouvé', 'favId' => $favId], 404);
    }
    // ...existing code...

    #[Route('/client/add-to-cart/{id}', name: 'add_to_cart', methods: ['POST'])]
    public function addToCart(int $id, Request $request): Response
    {
        // TODO: Ajout au panier (à adapter selon ta logique)
        // Pour l'instant, simple redirection
        return $this->redirectToRoute('app_client_favoris');
    }

    #[Route('/client/follow/toggle/{sellerId}', name: 'toggle_follow_seller', methods: ['POST'])]
    public function toggleFollowSeller(
        int $sellerId,
        Request $request,
        UsersRepository $usersRepository,
        \App\Repository\FollowSellerRepository $followRepo,
        \Doctrine\ORM\EntityManagerInterface $em
    ): JsonResponse {
        $session = $request->getSession();
        $userSession = $session->get('user');
        if (!$userSession) {
            return $this->json(['success' => false, 'message' => 'Non connecté'], 401);
        }

        $client = $usersRepository->find($userSession->getId());
        $seller = $usersRepository->find($sellerId);
        if (!$seller) {
            return $this->json(['success' => false, 'message' => 'Vendeur introuvable'], 404);
        }
        if ($client && $seller && $client->getId() === $seller->getId()) {
            return $this->json(['success' => false, 'message' => 'Impossible de se suivre soi-même'], 400);
        }

        $existing = $followRepo->findOneBy(['client' => $client, 'seller' => $seller]);
        if ($existing) {
            $em->remove($existing);
            $em->flush();
            return $this->json(['success' => true, 'action' => 'unfollowed']);
        }

        $follow = new \App\Entity\FollowSeller();
        $follow->setClient($client);
        $follow->setSeller($seller);
        $follow->setDateFollowing(new \DateTime());
        $em->persist($follow);
        $em->flush();

        return $this->json(['success' => true, 'action' => 'followed']);
    }

    #[Route('/client/remove-favorite/{itemSizeId}', name: 'remove_favorite', methods: ['POST'])]
    public function removeFavorite(
        int $itemSizeId,
        Request $request,
        UsersRepository $usersRepository,
        FavoritesRepository $favRepo,
        FavoriteDetailsRepository $favDetailRepo
    ): JsonResponse {
        $session = $request->getSession();
        $userSession = $session->get('user');
        if (!$userSession) {
            return $this->json(['success' => false, 'message' => 'Non connecté'], 401);
        }
        $user = $usersRepository->find($userSession->getId());
        $favorite = $favRepo->findOneBy(['client' => $user]);
        if (!$favorite) {
            return $this->json(['success' => false, 'message' => 'Favori non trouvé'], 404);
        }
        try {
            $favDetail = $favDetailRepo->findOneBy(['favorites' => $favorite, 'itemSize' => $itemSizeId]);
            if ($favDetail) {
                $favDetailRepo->remove($favDetail, true);
                return $this->json(['success' => true, 'action' => 'removed']);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Favori non trouvé',
                    'debug' => [
                        'favoriteId' => $favorite->getId(),
                        'itemSizeId' => $itemSizeId
                    ]
                ], 404);
            }
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Exception',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    #[Route('/client', name: 'app_client')]
    public function index(
        Request $request,
        CategoryRepository $categoryRepository,
        UsersRepository $usersRepository,
        SaleRepository $saleRepository,
        LiveRepository $liveRepository
    ): Response
    {
        $session = $request->getSession();
        $userSession = $session->get('user');

        if (!$userSession) {
            return $this->redirectToRoute('app_connection');
        }

        $user = $usersRepository->find($userSession->getId());
        if (!$user) {
            return $this->redirectToRoute('app_connection');
        }

        $ongoingLives = $liveRepository->findOnGoingLives();

        $lives = array_map(function($live) {
            $seller = $live->getSeller();
            $defaultThumbnail = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='640' height='360'%3E%3Crect width='100%25' height='100%25' fill='%23f0f0f0'/%3E%3Ccircle cx='320' cy='140' r='60' fill='%23bdbdbd'/%3E%3Cpath d='M220 270c0-55 50-90 100-90s100 35 100 90v20H220z' fill='%23bdbdbd'/%3E%3C/svg%3E";

            return [
                'id' => $live->getId(),
                'title' => 'Live en cours',
                'thumbnail' => ($seller && $seller->getImages()) ? ('/uploads/' . $seller->getImages()) : $defaultThumbnail,
                'language' => 'FR',
                'viewers' => 200,
                'username' => $seller ? $seller->getUsername() : 'Username',
                'isLive' => $live->getEndLive() === null,
            ];
        }, $ongoingLives);

        return $this->render('client/index.html.twig', [
            'userId' => $user->getId(),
            'user' => $user,
            'followedLives' => $lives,
            'recommendedLives' => $lives,
        ]);
    }

    #[Route('/client/live/{id}', name: 'app_client_live')]
    public function live(
    Request $request,
    UsersRepository $usersRepository,
    LiveRepository $liveRepository,
    LiveDetailsRepository $liveDetailsRepository,
    FavoritesRepository $favRepo,
    FavoriteDetailsRepository $favDetailRepo,
    ItemSizeRepository $itemSizeRepo,
    \App\Repository\FollowSellerRepository $followRepo,
    int $id
    ): Response
    {
        $session = $request->getSession();
        $userSession = $session->get('user');
        $currentUser = null;
        if ($userSession) {
            $currentUser = $usersRepository->find($userSession->getId());
        }

        $live = $liveRepository->find($id);
        if (!$live || $live->getEndLive() !== null) {
            return $this->redirectToRoute('app_client');
        }

        // Récupère les entités Item associées au live
        $items = $liveDetailsRepository->findBy(['live' => $live]);

        // Récupérer les favoris de l'utilisateur
        $favorisIds = [];
        $favorisMap = [];
        if ($currentUser) {
            $favorite = $favRepo->findOneBy(['client' => $currentUser]);
            if ($favorite) {
                $details = $favDetailRepo->findBy(['favorites' => $favorite]);
                foreach ($details as $detail) {
                    $itemSize = $detail->getItemSize();
                    $item = $itemSize->getItem();
                    $favorisIds[] = $item->getId();
                    $favorisMap[$item->getId()] = $detail->getId();
                }
            }
        }
        $isFollowing = false;
        if ($currentUser && $live && $live->getSeller()) {
            $follow = $followRepo->findOneBy(['client' => $currentUser, 'seller' => $live->getSeller()]);
            $isFollowing = $follow ? true : false;
        }

        return $this->render('client/live.html.twig', [
            'user' => $currentUser,
            'live' => $live,
            'items' => $items,
            'favorisIds' => $favorisIds,
            'favorisMap' => $favorisMap,
            'isFollowing' => $isFollowing,
        ]);
    }

    #[Route('/client/favoris', name: 'app_client_favoris')]
    public function favoris(
        FavoritesRepository $favRepo,
        FavoriteDetailsRepository $favDetailRepo,
        UsersRepository $usersRepository,
        Request $request
    ): Response
    {
        $session = $request->getSession();
        $userSession = $session->get('user');
        if (!$userSession) {
            return $this->redirectToRoute('app_connection');
        }
        $user = $usersRepository->find($userSession->getId());

        // Regrouper les favoris par article pour éviter les doublons
        $grouped = [];
        $favorites = $favRepo->findBy(['client' => $user]);
        foreach ($favorites as $favorite) {
            $details = $favDetailRepo->findBy(['favorites' => $favorite]);
            foreach ($details as $detail) {
                $itemSize = $detail->getItemSize();
                $item = $itemSize->getItem();
                $itemId = $item->getId();

                if (!isset($grouped[$itemId])) {
                    // Récupérer le dernier prix
                    $price = null;
                    $priceItems = $item->getPriceItems();
                    if (count($priceItems) > 0) {
                        $lastPrice = null;
                        foreach ($priceItems as $p) {
                            if ($lastPrice === null || $p->getDatePrice() > $lastPrice->getDatePrice()) {
                                $lastPrice = $p;
                            }
                        }
                        if ($lastPrice) {
                            $price = $lastPrice->getPrice();
                        }
                    }

                    $grouped[$itemId] = [
                        'favoriteId' => $favorite->getId(),
                        'item' => $item,
                        'price' => $price,
                        'description' => $item->getDescription() ?: 'Pas de description disponible',
                        'sizes' => []
                    ];
                }

                $grouped[$itemId]['sizes'][] = [
                    'sizeId' => $itemSize->getId(),
                    'sizeLabel' => $itemSize->getValueSize() . ($itemSize->getSize() ? ' (' . $itemSize->getSize()->getNameSize() . ')' : ''),
                ];
            }
        }

        return $this->render('client/favoris.html.twig', [
            'favoris' => array_values($grouped)
        ]);
    }

   #[Route('/client/favorite/toggle/{itemSizeId}', name: 'toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(
        int $itemSizeId,
        UsersRepository $usersRepository,
        FavoritesRepository $favRepo,
        FavoriteDetailsRepository $favDetailRepo,
        ItemSizeRepository $itemSizeRepo,
        Request $request
    ): JsonResponse
    {
        $session = $request->getSession();
        $userSession = $session->get('user');

        // --- TEMPORAIRE pour tests Postman ---
        if (!$userSession) {
            $userSession = $usersRepository->find(7); // Anthony
            $session->set('user', $userSession);
        }

        $user = $usersRepository->find($userSession->getId());

        $itemSize = $itemSizeRepo->find($itemSizeId);
        if (!$itemSize) {
            return $this->json(['success' => false, 'message' => 'Item introuvable'], 404);
        }

        // Cherche l'entrée Favorites existante pour ce client
        $favorite = $favRepo->findOneBy(['client' => $user]);
        if (!$favorite) {
            $favorite = new Favorites();
            $favorite->setClient($user);
            $favorite->setCreateAt(new \DateTime());
            $favRepo->save($favorite, true);
        }

        // Vérifier si déjà ajouté
        $exists = $favDetailRepo->findOneBy(['favorites' => $favorite, 'itemSize' => $itemSize]);
        if (!$exists) {
            $favDetail = new FavoriteDetails();
            $favDetail->setFavorites($favorite);
            $favDetail->setItemSize($itemSize);
            $favDetailRepo->save($favDetail, true);
        }

        return $this->json(['success' => true, 'action' => 'added']);
    }

}
