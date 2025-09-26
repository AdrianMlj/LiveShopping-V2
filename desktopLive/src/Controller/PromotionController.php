<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\HistoryRepository;
use App\Repository\StateCommandeRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class PromotionController extends AbstractController
{
    public function __construct(
        private PaginatorInterface $paginator
    ) {}

    #[Route('/promotion', name: 'app_promotion')]
    public function index()
    {

    }
}
