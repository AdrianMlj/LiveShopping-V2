<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\CountryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class ProfilAdminController extends AbstractController
{
    #[Route('/profil', name: 'app_admin_profil')]
    public function profil(Request $request, EntityManagerInterface $em, CountryService $countryService): Response
    {
        $session = $request->getSession();
        $userSession = $session->get('user');

        if (!$userSession) {
            return $this->redirectToRoute('app_connection');
        }

        /** @var Users $user */
        $user = $em->getRepository(Users::class)->find($userSession['id']);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $imagePath = null;
        if ($user->getImages()) {
            $imagePath = '/uploads/' . $user->getImages();
        }

        $countries = $countryService->getCountries();

        return $this->render('admin/profil.html.twig', [
            'user' => $user,
            'imagePath' => $imagePath,
            'countries' => $countries,
        ]);
    }

    #[Route('profil/update', name: 'app_admin_update_profile', methods: ['POST'])]
    public function updateProfil(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $userSession = $session->get('user');

        if (!$userSession) {
            return $this->redirectToRoute('app_connection');
        }

        /** @var Users $user */
        $user = $em->getRepository(Users::class)->find($userSession['id']);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $address = $request->request->get('address');
        $country = $request->request->get('country');
        $contact = $request->request->get('contact');

        if (empty($username)) {
            $this->addFlash('error', 'Le nom d\'utilisateur ne peut pas être vide');
            return $this->redirectToRoute('app_admin_profil');
        }

        if (empty($email)) {
            $this->addFlash('error', 'L\'email ne peut pas être vide');
            return $this->redirectToRoute('app_admin_profil');
        }

        /** @var UploadedFile|null $imageFile */
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            // Supprimer l'ancienne image si elle existe
            $oldImage = $user->getImages();
            if ($oldImage) {
                $oldImagePath = $this->getParameter('uploads_directory') . '/' . $oldImage;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Sauvegarder la nouvelle image
            $imageName = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($this->getParameter('uploads_directory'), $imageName);
            $user->setImages($imageName);
        }


        $user->setUsername($username);
        $user->setEmail($email);
        $user->setAddress($address);
        $user->setCountry($country);
        $user->setContact($contact);

        $em->flush();

        $userSession['username'] = $username;
        $session->set('user', $userSession);

        $this->addFlash('success', 'Profil mis à jour avec succès');
        return $this->redirectToRoute('app_admin_profil');
    }

    #[Route('/api/admin/profil', name: 'api_admin_profil', methods: ['GET'])]
    public function apiProfil(Request $request, EntityManagerInterface $em): Response
    {
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

        return $this->json([
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'address' => $user->getAddress(),
            'country' => $user->getCountry(),
            'contact' => $user->getContact(),
            'image' => $user->getImages(),
        ]);
    }

    #[Route('/api/admin/profil/update', name: 'api_admin_update_profile', methods: ['POST'])]
    public function apiUpdateProfil(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $userSession = $session->get('user');

        if (!$userSession) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Données JSON invalides'], 400);
        }

        /** @var Users $user */
        $user = $em->getRepository(Users::class)->find($userSession['id']);

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        if (empty($data['username']) || empty($data['email'])) {
            return $this->json(['error' => 'Champs obligatoires manquants'], 400);
        }

        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setAddress($data['address'] ?? null);
        $user->setCountry($data['country'] ?? null);
        $user->setContact($data['contact'] ?? null);

        $em->flush();

        $userSession['username'] = $data['username'];
        $session->set('user', $userSession);

        return $this->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'address' => $user->getAddress(),
                'country' => $user->getCountry(),
                'contact' => $user->getContact(),
                'image' => $user->getImages(),
            ]
        ]);
    }
}
