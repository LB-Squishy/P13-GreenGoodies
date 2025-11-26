<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    public function __construct(
        private CartItemRepository $cartItemRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route('/panier', name: 'app_cart_show')]
    public function index(): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $user->getCart();
        $cartItems = [];
        $cartTotalPrice = 0.00;

        if ($cart) {
            $cartItems = $cart->getCartItems();
            foreach ($cartItems as $cartItem) {
                $cartTotalPrice += $cartItem->getProduct()->getPrice() * $cartItem->getQuantity();
            }
        }

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'cartTotalPrice' => $cartTotalPrice,
        ]);
    }
}
