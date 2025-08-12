<?php

namespace App\Controller;

use App\Entity\Users;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Service\InscriptionService;
use App\Form\InscriptionFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function index(
        Request $request,
        InscriptionService $registrationService
    ): Response {
        $user = new Users();
        $form = $this->createForm(InscriptionFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $registrationService->register(
                $user,
                $form->get('plainPassword')->getData(),
                $request
            );
        }

        return $this->render('inscription/index.html.twig', [
            'inscriptionForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/api/inscription', name: 'api_inscription', methods: ['POST'])]
    public function apiRegister(
        Request $request,
        InscriptionService $registrationService
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'JSON invalide'], 400);
        }

        $required = ['username', 'email', 'password', 'contact', 'address', 'country'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->json(['error' => "Champ manquant : $field"], 400);
            }
        }

        $user = new Users();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setContact($data['contact']);
        $user->setAddress($data['address']);
        $user->setCountry($data['country']);
        $user->setIsSeller($data['is_seller'] ?? false);

        $registrationService->register($user, $data['password'], $request);

        return $this->json([
            'message' => 'Inscription réussie',
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
        ], 201);
    }
}
