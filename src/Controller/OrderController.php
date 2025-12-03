<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class OrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route('/compte', name: 'app_order_show')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        $orders = $user->getOrders();

        return $this->render('account/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/compte/commande/ajouter', name: 'app_order_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addCartInOrder(Request $request): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        // Vérifie le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('order_add', $token)) {
            flash()->error('Une erreur est survenue.');
            return $this->redirectToRoute('app_cart_show');
        }

        // Récupération du panier de l'utilisateur
        $cart = $user->getCart();
        if (!$cart) {
            flash()->error('Votre panier est vide.');
            return $this->redirectToRoute('app_cart_show');
        }

        // Création de la commande
        $order = new Order();
        $order->setUser($user);
        $year = (new \DateTime())->format('Y');
        $month = (new \DateTime())->format('m');
        $day = (new \DateTime())->format('d');
        $userId = $user->getId();
        $random = mt_rand(1000, 9999);
        $reference = sprintf('%s-%s%s%s%s', $year, $month, $day, $userId, $random);
        $order->setReference($reference);
        $totalPrice = 0.00;

        // Memorisation des Produits du panier dans la commande
        foreach ($cart->getCartItems() as $cartItem) {
            $product = $cartItem->getProduct();
            $orderItem = new OrderItem();
            $orderItem->setPurchase($order);
            $orderItem->setProduct($product);
            $orderItem->setProductName($product->getName());
            $orderItem->setProductPicture($product->getPicture());
            $orderItem->setUnitPrice($product->getPrice());
            $orderItem->setQuantity($cartItem->getQuantity());
            $totalPrice += $product->getPrice() * $cartItem->getQuantity();
            $this->entityManager->persist($orderItem);
        }

        $order->setTotalPrice($totalPrice);
        $this->entityManager->persist($order);

        // Vidage du panier
        foreach ($cart->getCartItems() as $cartItem) {
            $this->entityManager->remove($cartItem);
        }

        $this->entityManager->flush();
        flash()->success('Votre commande a bien été prise en compte !');

        return $this->redirectToRoute('app_order_show');
    }
}
