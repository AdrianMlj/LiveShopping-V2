<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LoginController extends AbstractController
{
    #[Route('/connection', name: 'app_connection')]
    public function login(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $error = null;

        if ($request->isMethod('POST')) {
            $logMessage = 'Formulaire POST reçu';
        } else {
            $logMessage = 'Page affichée sans soumission';
        }

        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $password = $request->request->get('password');

            /** @var Users|null $user */
            $user = $em->getRepository(Users::class)->findOneBy(['username' => $username]);

            if (!$user) {
                $error = 'Nom d’utilisateur invalide.';
            } elseif (!$hasher->isPasswordValid($user, $password)) {
                $error = 'Mot de passe incorrect.';
            } else {
                // Authentification réussie : stocke les infos en session
                $session = $request->getSession();
                $session->set('user', $user);

                $this->addFlash('success', 'Connexion réussie !');
                if ($user->isSeller()) {
                    return $this->redirectToRoute('app_dashboard'); // dashboard/index.html.twig
                } else {
                    return $this->redirectToRoute('app_client'); // client/index.html.twig
                }
            }
        }

        return $this->render('login/index.html.twig', [
            'error' => $error,
            'log_message' => $logMessage,
        ]);
    }
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function apiLogin(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'JSON invalide'], 400);
        }

        if (empty($data['username']) || empty($data['password'])) {
            return $this->json(['error' => 'Nom d’utilisateur ou mot de passe manquant'], 400);
        }

        /** @var Users|null $user */
        $user = $em->getRepository(Users::class)->findOneBy(['username' => $data['username']]);

        if (!$user) {
            return $this->json(['error' => 'Nom d’utilisateur invalide.'], 401);
        }

        if (!$hasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Mot de passe incorrect.'], 401);
        }

        // Authentification réussie
        return $this->json([
            'message' => 'Connexion réussie',
            'user' => [
                'id_user' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'contact' => $user->getContact(),
                'address' => $user->getAddress(),
                'country' => $user->getCountry(),
                'image' => $user->getImages(),
                'is_seller' => $user->isSeller(),
            ]
        ]);
    }
}
