<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\CountryService;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class ProfilController extends AbstractController
{
    #[Route('/users/profil', name: 'app_users_profil')]
    public function profil(Request $request, EntityManagerInterface $em, CountryService $countryService): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');

        if (!$user) {
            return $this->redirectToRoute('app_connection');
        }

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $imagePath = null;
        if ($user->getImages()) {
            $imagePath = '/uploads/' . $user->getImages();
        }

        $countries = $countryService->getCountries();

        if ($user->isSeller()) {
            return $this->render('admin/profil.html.twig', [
                'user' => $user,
                'imagePath' => $imagePath,
                'countries' => $countries,
            ]);
        }

        return $this->render('client/profil.html.twig', [
            'user' => $user,
            'imagePath' => $imagePath,
            'countries' => $countries,
        ]);
    }

    #[Route('/users/profil/update', name: 'app_users_update_profile', methods: ['POST'])]
    public function updateProfil(Request $request, EntityManagerInterface $em, UsersRepository $userRepository): Response
    {
        $session = $request->getSession();
        $userData = $session->get('user') ?? null;

        if (!$userData) {
            return $this->redirectToRoute('app_connection');
        }

        // Récupérer l'entité depuis la base de données
        $user = $userRepository->find($userData->getId());

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $address = $request->request->get('address');
        $country = $request->request->get('country');
        $contact = $request->request->get('contact');

        // Validation
        if (empty($username)) {
            $this->addFlash('error', 'Le nom d\'utilisateur ne peut pas être vide');
            return $this->redirectToRoute('app_client_profil');
        }

        if (empty($email)) {
            $this->addFlash('error', 'L\'email ne peut pas être vide');
            return $this->redirectToRoute('app_client_profil');
        }

        // Gestion de l'upload d'image
        /** @var UploadedFile|null $imageFile */
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            try {
                $imageName = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('uploads_directory'), $imageName);
                $user->setImages($imageName);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                return $this->redirectToRoute('app_client_profil');
            }
        }

        // Mise à jour des données
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setAddress($address);
        $user->setCountry($country);
        $user->setContact($contact);

        try {
            // Persist l'entité (au cas où)
            $em->persist($user);
            $em->flush();

            // Mettre à jour la session avec les nouvelles données
            $session->set('user', $user);

            $this->addFlash('success', 'Profil mis à jour avec succès');
            return $this->redirectToRoute('app_users_profil');

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la mise à jour du profil');
            return $this->redirectToRoute('app_client_profil');
        }
    }

    #[Route('/api/client/profil', name: 'api_client_profil', methods: ['GET'])]
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

    #[Route('/api/client/profil/update', name: 'api_client_update_profile', methods: ['POST'])]
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
