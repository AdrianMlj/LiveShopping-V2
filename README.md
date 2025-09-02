# LiveShopping
php -S localhost:8000 -t public


php bin/console make:migration
php bin/console doctrine:migrations:migrate

composer require symfony/security-bundle
composer require symfony/form symfony/validator
php bin/console make:form InscriptionForm 
php bin/console make:controller InscriptionController 
composer require symfony/http-
composer require symfony/asset
php bin/console make:
npm install chart.js
composer require symfony/mime
composer require symfony/firebase-notifier


npm install ws

symfony serve --allow-http --port=8000 --allow-all-ip
netsh advfirewall firewall add rule name="WebSocket" dir=in action=allow protocol=TCP localport=9090



Redis-cli 

composer require symfony/redis-messenger
composer require predis/predis

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategoryRepository;
use App\Repository\SaleRepository;
use App\Repository\LiveRepository;
use App\Repository\UsersRepository;
use App\Entity\Users;
use App\Entity\Live;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ClientController extends AbstractController
{
    #[Route('/client', name: 'app_client')]
    public function index(Request $request,
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

        // Lives en cours depuis la base (endLive IS NULL)
        $ongoingLives = $liveRepository->findOnGoingLives();

        // Map vers une structure simple attendue par le template
        $lives = array_map(function($live) {
            /** @var \App\Entity\Live $live */
            $seller = $live->getSeller();
            // Image par défaut: icône utilisateur (silhouette) en SVG (data URI), neutre
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
    public function live(Request $request, UsersRepository $usersRepository, LiveRepository $liveRepository, int $id): Response
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

        return $this->render('client/live.html.twig', [
            'user' => $currentUser,
            'live' => $live,
        ]);
    }
}