<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\ItemRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class PromotionController extends AbstractController
{
    public function __construct(
        private PaginatorInterface $paginator,
        private ItemRepository $itemRepository,
        private CategoryRepository $categoryRepository
    ) {}

    #[Route('/promotion', name: 'app_promotion', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');
        if (!$user) {
            return $this->redirectToRoute('app_connection');
        }

        $categoryId = $request->query->get('category_id');
        $categoryId = is_numeric($categoryId) ? (int)$categoryId : null;

        $rows = $this->itemRepository->findItemsWithAvailableSizesGrouped($user->getId(), $categoryId);
        $categories = $this->categoryRepository->findAll();

        return $this->render('admin/promotion.html.twig', [
            'rows' => $rows,
            'category_id' => $categoryId,
            'categories' => $categories,
        ]);
    }
}
