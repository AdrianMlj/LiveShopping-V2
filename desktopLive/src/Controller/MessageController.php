<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class MessageController extends AbstractController
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    #[Route('/messages', name: 'app_messages', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Récupère l'utilisateur connecté depuis la session
        $session = $request->getSession();
        $user = $session->get('user');

        return $this->render('messages/index.html.twig', [
            'user' => $user, // ← ton utilisateur connecté
        ]);
    }

    #[Route('/messages/send', name: 'send_message', methods: ['POST'])]
    public function sendMessage(Request $request): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');
        if (!$user) {
            return $this->json(['status' => 'error', 'message' => 'Utilisateur non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $messages = $this->cache->get('messages', fn() => []);

        $messages[] = [
            'user' => [
                'id_user' => $user->getId(),
                'username' => $user->getUsername(),
                'image' => $user->getImages(),
            ],
            'message' => $data['message'] ?? '',
            'time' => date('H:i')
        ];

        $this->cache->delete('messages'); // supprimer pour réécrire
        $this->cache->get('messages', fn() => $messages);

        return $this->json(['status' => 'ok', 'messages' => $messages]);
    }
}
