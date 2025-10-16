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
        $sizesByItem = [];
        $colorsByProduct = [];
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

            // Récupérer les tailles et couleurs par taille
            $sizes = [];
            $colorsMapForItem = [];
            foreach ($item->getItemSizes() as $sizeObj) {
                $sizes[] = [
                    'id' => $sizeObj->getId(),
                    'value' => $sizeObj->getValueSize()
                ];

                // colors for this size
                $sizeColors = [];
                foreach ($sizeObj->getItemSizeColors() as $isc) {
                    $color = $isc->getColor();
                    if ($color) {
                        // Try to surface an image for this size/color variant (item_size_color.images)
                        $img = null;
                        try {
                            if (method_exists($isc, 'getImages')) {
                                $raw = $isc->getImages();
                                if ($raw) {
                                    $img = '/uploads/' . ltrim($raw, '/');
                                }
                            }
                        } catch (\Throwable $e) {
                            // ignore
                        }

                        $sizeColors[] = [
                            'id' => $color->getId(),
                            'name' => $color->getNameColor() ?? ('#'.$color->getId()),
                            'image' => $img,
                        ];
                    }
                }
                $colorsMapForItem[$sizeObj->getId()] = $sizeColors;
            }
            if (!empty($sizes)) {
                $sizesByItem[$item->getId()] = $sizes;
                $colorsByProduct[$item->getId()] = $colorsMapForItem;
            }

            $sellerName = $item->getSeller() ? $item->getSeller()->getUsername() : 'N/A';
            $products[] = [
                'id' => $item->getId(),
                'name' => $item->getNameItem(),
                'images' => $item->getImages(),
                'price' => $price,
                'sizes' => $sizes,
                'description' => $item->getDescription() ?: 'Pas de description disponible',
                'seller' => $sellerName
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
            'sizesByItem' => $sizesByItem,
            'colorsByProduct' => $colorsByProduct,
        ]);
    }
}
