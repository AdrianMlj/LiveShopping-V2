<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $em): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        // Utilisateur fictif (à remplacer par session ou authentification)
        $user = [
            'username' => 'demo',
            'fidelity' => 10
        ];

        // Favoris et panier fictifs (à remplacer par logique réelle)
        $favorites = [];
        $cart = [];

        // Filtres
        $catFilter = $request->query->get('cat');
        $minPrice = $request->query->get('minprice');
        $maxPrice = $request->query->get('maxprice');

        // Récupérer les produits
        $items = $em->getRepository(\App\Entity\Item::class)->findAll();
        $products = [];
        foreach ($items as $item) {
            // Récupérer le dernier prix
            $priceObj = $em->getRepository(\App\Entity\PriceItems::class)->findOneBy([
                'item' => $item
            ], ['datePrice' => 'DESC']);
            $price = $priceObj ? $priceObj->getPrice() : null;

            // Filtrage catégorie
            if ($catFilter && strtolower($item->getCategory()->getNameCategory()) !== strtolower($catFilter)) {
                continue;
            }
            // Filtrage prix min
            if ($minPrice && $price && $price < $minPrice) {
                continue;
            }
            // Filtrage prix max
            if ($maxPrice && $price && $price > $maxPrice) {
                continue;
            }

            // Récupérer les tailles
            $sizes = [];
            foreach ($item->getItemSizes() as $sizeObj) {
                $sizes[] = [
                    'id' => $sizeObj->getId(),
                    'value' => $sizeObj->getValueSize()
                ];
            }

            // Rating moyen et nombre
            $ratingRepo = $em->getRepository(\App\Entity\Rating::class);
            $avg = null; $count = 0;
            if ($ratingRepo) {
                $stats = $em->getRepository(\App\Entity\Rating::class)->createQueryBuilder('r')
                    ->select('AVG(r.value) AS avg_rating, COUNT(r.id) AS cnt')
                    ->andWhere('r.item = :item')
                    ->setParameter('item', $item)
                    ->getQuery()->getOneOrNullResult();
                $avg = $stats && $stats['avg_rating'] !== null ? (float)$stats['avg_rating'] : null;
                $count = $stats ? (int)$stats['cnt'] : 0;
            }

            $sellerName = $item->getSeller() ? $item->getSeller()->getUsername() : 'N/A';
            $products[] = [
                'id' => $item->getId(),
                'name' => $item->getNameItem(),
                'images' => $item->getImages(),
                'price' => $price,
                'sizes' => $sizes,
                'description' => $item->getDescription() ?: 'Pas de description disponible',
                'seller' => $sellerName,
                'rating' => $avg ? round($avg) : 0,
                'ratingAvg' => $avg,
                'ratingCount' => $count,
            ];
        }

        // Récupérer les catégories
        $categories = $em->getRepository(\App\Entity\Category::class)->findAll();
        $categoriesArr = [];
        foreach ($categories as $cat) {
            $categoriesArr[] = $cat->getNameCategory();
        }

        return $this->render('home/index.html.twig', [
            'user' => $user,
            'favorites' => $favorites,
            'cart' => $cart,
            'products' => $products,
            'categories' => $categoriesArr,
        ]);
    }
}
