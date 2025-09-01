<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Routing\Annotation\Route;

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
        // Si c'est une requête AJAX (fetch), renvoyer le JSON
        if ($request->isXmlHttpRequest()) {
            $messages = $this->cache->get('messages', fn() => []);
            return $this->json($messages);
        }

        // Sinon, afficher le template HTML du messages
        return $this->render('messages/index.html.twig'); // ton fichier HTML
    }

    #[Route('/messages/send', name: 'send_message', methods: ['POST'])]
    public function sendMessage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $messages = $this->cache->get('messages', fn() => []);

        $messages[] = [
            'user' => $data['user'],
            'message' => $data['message'],
            'time' => date('H:i')
        ];

        $this->cache->delete('messages'); // supprimer pour réécrire
        $this->cache->get('messages', fn() => $messages);

        return $this->json(['status' => 'ok', 'messages' => $messages]);
    }
}
