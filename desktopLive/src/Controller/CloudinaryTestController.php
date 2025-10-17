<?php

namespace App\Controller;

use App\Service\CloudinaryService;
use App\Entity\Users;
use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de test pour Cloudinary
 * Ce contrôleur permet de tester l'upload d'images vers Cloudinary
 */
final class CloudinaryTestController extends AbstractController
{
    #[Route('/test/cloudinary/upload-form', name: 'cloudinary_test_form')]
    public function uploadForm(): Response
    {
        return $this->render('test/cloudinary_test.html.twig');
    }

    /**
     * Test d'upload d'image vers Cloudinary
     */
    #[Route('/test/cloudinary/upload', name: 'cloudinary_test_upload', methods: ['POST'])]
    public function testUpload(Request $request, CloudinaryService $cloudinaryService): Response
    {
        try {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('image');

            if (!$imageFile) {
                $this->addFlash('error', 'Aucune image sélectionnée');
                return $this->redirectToRoute('cloudinary_test_form');
            }

            // Upload vers Cloudinary
            $imageUrl = $cloudinaryService->uploadImage($imageFile, 'test_uploads');

            $this->addFlash('success', 'Image uploadée avec succès !');
            $this->addFlash('image_url', $imageUrl);

            return $this->redirectToRoute('cloudinary_test_form');

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'upload : ' . $e->getMessage());
            return $this->redirectToRoute('cloudinary_test_form');
        }
    }

    /**
     * Test via API JSON
     */
    #[Route('/api/test/cloudinary/upload', name: 'api_cloudinary_test_upload', methods: ['POST'])]
    public function apiTestUpload(Request $request, CloudinaryService $cloudinaryService): Response
    {
        try {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('image');

            if (!$imageFile) {
                return $this->json([
                    'success' => false,
                    'error' => 'Aucune image fournie'
                ], 400);
            }

            $folder = $request->request->get('folder', 'test_uploads');
            $imageUrl = $cloudinaryService->uploadImage($imageFile, $folder);

            return $this->json([
                'success' => true,
                'message' => 'Image uploadée avec succès',
                'image_url' => $imageUrl,
                'folder' => $folder
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exemple d'utilisation pour un profil utilisateur
     */
    #[Route('/test/cloudinary/user-profile-update', name: 'cloudinary_user_profile_test', methods: ['POST'])]
    public function testUserProfileUpdate(
        Request $request,
        CloudinaryService $cloudinaryService,
        EntityManagerInterface $em
    ): Response {
        $session = $request->getSession();
        $userSession = $session->get('user');

        if (!$userSession) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        /** @var Users $user */
        $user = $em->getRepository(Users::class)->find($userSession['id']);

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        try {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('image');

            if ($imageFile) {
                // Si l'utilisateur a déjà une image sur Cloudinary, on pourrait la supprimer
                // (nécessite d'extraire le publicId de l'ancienne URL)

                // Upload de la nouvelle image
                $imageUrl = $cloudinaryService->uploadImage($imageFile, 'users/profiles');

                // Stocker l'URL complète Cloudinary dans la base
                $user->setImages($imageUrl);

                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Photo de profil mise à jour',
                    'image_url' => $imageUrl
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => 'Aucune image fournie'
            ], 400);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'upload : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exemple d'utilisation pour un article (Item)
     */
    #[Route('/test/cloudinary/item-image-update/{id}', name: 'cloudinary_item_image_test', methods: ['POST'])]
    public function testItemImageUpdate(
        int $id,
        Request $request,
        CloudinaryService $cloudinaryService,
        EntityManagerInterface $em
    ): Response {
        /** @var Item $item */
        $item = $em->getRepository(Item::class)->find($id);

        if (!$item) {
            return $this->json(['error' => 'Article non trouvé'], 404);
        }

        try {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('image');

            if ($imageFile) {
                // Upload de l'image de l'article
                $imageUrl = $cloudinaryService->uploadImage($imageFile, 'items');

                // Stocker l'URL Cloudinary
                $item->setImages($imageUrl);

                $em->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Image de l\'article mise à jour',
                    'image_url' => $imageUrl,
                    'item_id' => $id
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => 'Aucune image fournie'
            ], 400);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'upload : ' . $e->getMessage()
            ], 500);
        }
    }
}

