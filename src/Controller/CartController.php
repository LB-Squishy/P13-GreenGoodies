<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Form\Cart\CartAddFormType;
use App\Form\Cart\CartRemoveAllFormType;
use App\Form\Cart\OrderAddFormType;
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
final class CartController extends AbstractController
{
    public function __construct(
        private CartItemRepository $cartItemRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route('', name: 'app_cart_show')]
    public function index(): Response
    {
        $cartRemoveAllForm = $this->createForm(CartRemoveAllFormType::class, null, [
            'action' => $this->generateUrl('app_cart_remove_all'),
        ]);
        $orderAddForm = $this->createForm(OrderAddFormType::class, null, [
            'action' => $this->generateUrl('app_order_add'),
        ]);

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
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
            'cartRemoveAllForm' => $cartRemoveAllForm,
            'orderAddForm' => $orderAddForm,
        ]);
    }

    #[Route('/ajouter/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function addItemInCart(?Product $product, Request $request): Response
    {
        // Vérifie si le produit est actif et existe
        if (!$product || !$product->isActive()) {
            flash()->error('Le produit demandé n\'existe pas ou n\'est plus disponible.');
            return $this->redirectToRoute('app_accueil');
        }

        $form = $this->createForm(CartAddFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User|null $user */
            $user = $this->getUser();

            // Récupération de la quantité depuis le formulaire
            $quantity = $form->get('quantity')->getData();

            // Récupération ou création du panier
            $cart = $user->getCart();
            if (!$cart) {
                $cart = new Cart();
                $user->setCart($cart);
                $this->entityManager->persist($cart);
            }

            // Récupération ou du produit dans le panier
            $cartItem = $this->cartItemRepository->findOneBy([
                'cart' => $cart,
                'product' => $product,
            ]);

            // Mise à jour ou ajout du produit dans le panier
            if ($cartItem) {
                // Mise à jour de la quantité
                if ($quantity > 0) {
                    $cartItem->setQuantity($quantity);
                    $this->entityManager->persist($cartItem);
                    flash()->success('La quantité du produit a été mise à jour dans votre panier.');
                } else {
                    // Suppression du produit du panier si la quantité est à zéro
                    $this->entityManager->remove($cartItem);
                    flash()->success('Le produit a été supprimé de votre panier.');
                }
            } else {
                // Ajout du produit au panier
                if ($quantity > 0) {
                    $cartItem = new CartItem();
                    $cartItem->setCart($cart);
                    $cartItem->setProduct($product);
                    $cartItem->setQuantity($quantity);
                    $this->entityManager->persist($cartItem);
                    flash()->success('Le produit a été ajouté à votre panier.');
                } else {
                    flash()->error('La quantité doit être supérieure à zéro pour ajouter un produit au panier.');
                }
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('app_cart_show');
        }
        flash()->error('Une erreur est survenue lors de l\'ajout du produit à votre panier.');
        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    #[Route('/vider', name: 'app_cart_remove_all', methods: ['POST'])]
    public function removeAllFromCart(Request $request): Response
    {
        $form = $this->createForm(CartRemoveAllFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User|null $user */
            $user = $this->getUser();

            // Récupération du panier
            $cart = $user->getCart();

            if ($cart) {
                // Récupération des produits du panier
                $cartItems = $cart->getCartItems();
                // Suppression de tous les produits du panier
                foreach ($cartItems as $cartItem) {
                    $this->entityManager->remove($cartItem);
                }
                $this->entityManager->flush();
                flash()->success('Tous les produits ont été supprimés de votre panier.');
            }

            return $this->redirectToRoute('app_cart_show');
        }
        flash()->error('Une erreur est survenue lors de la suppression des produits de votre panier.');
        return $this->redirectToRoute('app_cart_show');
    }
}
