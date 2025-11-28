<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CartItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private CartItemRepository $cartItemRepository,
    ) {}

    #[Route('/', name: 'app_accueil')]
    public function index(): Response
    {
        $products = $this->productRepository->findBy(
            ['isActive' => true],
            ['createdAt' => 'DESC'],
            9
        );

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/produit/{id}', name: 'app_product_show')]
    public function showProduct(?Product $product): Response
    {
        if (!$product || !$product->isActive()) {
            throw $this->createNotFoundException('Le produit demandé n\'existe pas ou n\'est plus disponible.');
        }

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        $quantityItemInCart = 0;

        if ($user) {
            $cart = $user->getCart();
            if ($cart) {
                $cartItem = $this->cartItemRepository->findOneBy([
                    'cart' => $cart,
                    'product' => $product
                ]);

                if ($cartItem) {
                    $quantityItemInCart = $cartItem->getQuantity();
                }
            }
        }

        return $this->render('product/product.html.twig', [
            'product' => $product,
            'quantityItemInCart' => $quantityItemInCart,
        ]);
    }
}
