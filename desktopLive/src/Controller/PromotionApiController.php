<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Promotion;
use App\Repository\ItemRepository;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/promotions', name: 'api_promotions_')]
class PromotionApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ItemRepository $itemRepository,
        private PromotionRepository $promotionRepository,
        private ValidatorInterface $validator
    ) {}

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function createPromotion(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation des données
            if (!isset($data['item_id']) || !isset($data['name']) || !isset($data['percentage']) || !isset($data['start_date'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données manquantes'
                ], 400);
            }

            $item = $this->itemRepository->find($data['item_id']);
            if (!$item) {
                return $this->json([
                    'success' => false,
                    'message' => 'Article non trouvé'
                ], 404);
            }

            // Vérifier s'il existe déjà une promotion active pour cet article
            $existingPromotion = $this->promotionRepository->findActivePromotionForItem($item->getId());
            if ($existingPromotion) {
                return $this->json([
                    'success' => false,
                    'message' => 'Une promotion est déjà active pour cet article'
                ], 400);
            }

            // Créer la nouvelle promotion
            $promotion = new Promotion();
            $promotion->setNamePromotion($data['name']);
            $promotion->setPercentage($data['percentage']);
            $promotion->setDescription($data['description'] ?? null);
            $promotion->setStartDate(new \DateTime($data['start_date']));

            if (isset($data['end_date']) && !empty($data['end_date'])) {
                $promotion->setEndDate(new \DateTime($data['end_date']));
            }

            $promotion->setItem($item);

            // Validation
            $errors = $this->validator->validate($promotion);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $errorMessages
                ], 400);
            }

            // Sauvegarder
            $this->entityManager->persist($promotion);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Promotion créée avec succès',
                'promotion' => [
                    'id' => $promotion->getId(),
                    'name' => $promotion->getNamePromotion(),
                    'percentage' => $promotion->getPercentage(),
                    'description' => $promotion->getDescription(),
                    'start_date' => $promotion->getStartDate()->format('Y-m-d'),
                    'end_date' => $promotion->getEndDate() ? $promotion->getEndDate()->format('Y-m-d') : null
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la promotion: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/update/{id}', name: 'update', methods: ['PUT'])]
    public function updatePromotion(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $promotion = $this->promotionRepository->find($id);
            if (!$promotion) {
                return $this->json([
                    'success' => false,
                    'message' => 'Promotion non trouvée'
                ], 404);
            }

            // Mettre à jour les champs
            if (isset($data['name'])) {
                $promotion->setNamePromotion($data['name']);
            }
            if (isset($data['percentage'])) {
                $promotion->setPercentage($data['percentage']);
            }
            if (isset($data['description'])) {
                $promotion->setDescription($data['description']);
            }
            if (isset($data['start_date'])) {
                $promotion->setStartDate(new \DateTime($data['start_date']));
            }
            if (isset($data['end_date'])) {
                if (!empty($data['end_date'])) {
                    $promotion->setEndDate(new \DateTime($data['end_date']));
                } else {
                    $promotion->setEndDate(null);
                }
            }

            // Validation
            $errors = $this->validator->validate($promotion);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $errorMessages
                ], 400);
            }

            // Sauvegarder
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Promotion mise à jour avec succès',
                'promotion' => [
                    'id' => $promotion->getId(),
                    'name' => $promotion->getNamePromotion(),
                    'percentage' => $promotion->getPercentage(),
                    'description' => $promotion->getDescription(),
                    'start_date' => $promotion->getStartDate()->format('Y-m-d'),
                    'end_date' => $promotion->getEndDate() ? $promotion->getEndDate()->format('Y-m-d') : null
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la promotion: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function deletePromotion(int $id): JsonResponse
    {
        try {
            $promotion = $this->promotionRepository->find($id);
            if (!$promotion) {
                return $this->json([
                    'success' => false,
                    'message' => 'Promotion non trouvée'
                ], 404);
            }

            $this->entityManager->remove($promotion);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Promotion supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la promotion: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/item/{itemId}', name: 'get_by_item', methods: ['GET'])]
    public function getPromotionByItem(int $itemId): JsonResponse
    {
        try {
            $promotion = $this->promotionRepository->findActivePromotionForItem($itemId);

            if (!$promotion) {
                return $this->json([
                    'success' => true,
                    'promotion' => null
                ]);
            }

            return $this->json([
                'success' => true,
                'promotion' => [
                    'id' => $promotion->getId(),
                    'name' => $promotion->getNamePromotion(),
                    'percentage' => $promotion->getPercentage(),
                    'description' => $promotion->getDescription(),
                    'start_date' => $promotion->getStartDate()->format('Y-m-d'),
                    'end_date' => $promotion->getEndDate() ? $promotion->getEndDate()->format('Y-m-d') : null
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la promotion: ' . $e->getMessage()
            ], 500);
        }
    }
}


