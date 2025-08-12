<?php

namespace App\Controller;

use App\Entity\Live;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/live', name: 'api_live_')]
class LiveApiController extends AbstractController
{
    #[Route('/start', name: 'start', methods: ['POST'])]
    public function startLive(
        Request $request,
        EntityManagerInterface $em,
        UsersRepository $usersRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $userId = $data['user_id'] ?? null;

        if (!$userId) {
            return $this->json(['error' => 'user_id est requis'], 400);
        }

        $user = $usersRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        // Vérifie si un live est déjà actif
        $activeLive = $em->getRepository(Live::class)->findOneBy([
            'seller' => $user,
            'endLive' => null
        ]);

        if ($activeLive) {
            return $this->json([
                'status' => 'error',
                'message' => 'Un live est déjà en cours.'
            ], 409);
        }

        $live = new Live();
        $live->setStartLive(new \DateTime());
        $live->setSeller($user);

        $em->persist($live);
        $em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Live démarré avec succès.',
            'live_id' => $live->getId(),
            'start_time' => $live->getStartLive()->format('Y-m-d H:i:s')
        ], 201);
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
}
