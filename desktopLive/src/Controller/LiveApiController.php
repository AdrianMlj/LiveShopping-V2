<?php

namespace App\Controller;

use App\Entity\Live;
use App\Entity\LiveDetails;
use App\Repository\UsersRepository;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/live', name: 'api_live_')]
class LiveApiController extends AbstractController
{
    #[Route('/formLive', name: 'formLive', methods: ['POST'])]
    public function apiStartLiveSelect(
        Request $request,
        ItemRepository $itemRepository,
    ): JsonResponse {
        $content = json_decode($request->getContent(), true);
        $userId = $content['user_id'] ?? null;

        $items = $itemRepository->findAvailableItems($userId);

        $data = array_map(function ($item) {
            return [
                'id' => $item['id_item'],
                'name' => $item['nameItem'],
                'image' => $item['images'],
                'category' => $item['nameCategory'],
                'stock' => $item['stock_disponible'],
                'price' => $item['prix'],
                'promotion' => [
                    'name' => $item['namePromotion'],
                    'percentage' => $item['percentage']
                ]
            ];
        }, $items);

        return $this->json([
            'success' => true,
            'items' => $data
        ]);
    }

    #[Route('/start', name: 'start', methods: ['POST'])]
    public function apiConfirmLive(
        Request $request,
        EntityManagerInterface $em,
        UsersRepository $usersRepository,
        ItemRepository $itemRepository
    ): JsonResponse {
        $content = json_decode($request->getContent(), true);
        $userId = $content['user_id'] ?? null;
        $user = $usersRepository->find($userId);

        $titre = $content['titre'] ?? null;
        $description = $content['description'] ?? null;
        $selectedItems = $content['items'] ?? [];

        if (!$titre || !is_array($selectedItems) || empty($selectedItems)) {
            return $this->json(['success' => false, 'message' => 'Titre et items obligatoires'], 400);
        }

        $live = new Live();
        $live->setStartLive(new \DateTime());
        $live->setSeller($user);
        $live->setTitre($titre);
        $live->setDescription($description);
        $em->persist($live);

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

        return $this->json([
            'success' => true,
            'live_id' => $live->getId(),
            'message' => 'Live créé avec succès'
        ]);
    }

    #[Route('/stop', name: 'stop', methods: ['POST'])]
    public function stopLive(
        Request $request,
        EntityManagerInterface $em,
        UsersRepository $usersRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $userId = $data['user_id'] ?? null;
        $liveId = $data['live_id'] ?? null;

        if (!$userId || !$liveId) {
            return $this->json(['error' => 'user_id et live_id sont requis'], 400);
        }

        $user = $usersRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $live = $em->getRepository(Live::class)->find($liveId);
        if (!$live) {
            return $this->json(['error' => 'Live introuvable'], 404);
        }

        if ($live->getSeller()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Vous ne pouvez pas arrêter ce live'], 403);
        }

        $live->setEndLive(new \DateTime());
        $em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Le live a été terminé avec succès.',
            'live_id' => $live->getId(),
            'end_time' => $live->getEndLive()->format('Y-m-d H:i:s')
        ], 200);
    }

    #[Route('/active', name: 'active', methods: ['GET'])]
    public function getActiveLives(EntityManagerInterface $em): JsonResponse
    {
        $lives = $em->getRepository(Live::class)->findActiveLives();

        $data = array_map(function (Live $live) {
            return [
                'id' => $live->getId(),
                'titre' => $live->getTitre(),
                'description' => $live->getDescription(),
                'start_time' => $live->getStartLive()->format('Y-m-d H:i:s'),
                'seller' => [
                    'id' => $live->getSeller()->getId(),
                    'name' => $live->getSeller()->getUsername()
                ]
            ];
        }, $lives);

        return $this->json([
            'success' => true,
            'lives' => $data
        ]);
    }
}
