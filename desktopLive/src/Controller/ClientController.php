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
            $userSession = $usersRepository->find(7); // Anthony
            $session->set('user', $userSession);
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
        return $this->render('client/live.html.twig', [
            'user' => $currentUser,
            'live' => $live,
            'items' => $items,
            'favorisIds' => $favorisIds,
            'favorisMap' => $favorisMap,
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

        $favorisData = [];
        $favorites = $favRepo->findBy(['client' => $user]);
        foreach ($favorites as $favorite) {
            $details = $favDetailRepo->findBy(['favorites' => $favorite]);
            foreach ($details as $detail) {
                $itemSize = $detail->getItemSize();
                $item = $itemSize->getItem();
                $favoriteId = $favorite->getId();
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
                $favorisData[] = [
                    'favoriteId' => $favoriteId,
                    'item' => $item,
                    'price' => $price,
                    'size' => [
                        'sizeId' => $itemSize->getId(),
                        'sizeLabel' => $itemSize->getValueSize() . ($itemSize->getSize() ? ' (' . $itemSize->getSize()->getNameSize() . ')' : ''),
                    ]
                ];
            }
        }

        return $this->render('client/favoris.html.twig', [
            'favoris' => $favorisData
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
