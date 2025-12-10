<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

#[Route('/compte')]
final class OrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage,
    ) {}

    #[Route('', name: 'app_order_show')]
    public function index(): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        $orders = $user->getOrders();

        return $this->render('account/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/commande/ajouter', name: 'app_order_add', methods: ['POST'])]
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

    #[Route(path: '/delete', name: 'app_delete_account', methods: ['POST'])]
    public function deleteAccount(Request $request): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        // Vérifie le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('app_delete_account', $token)) {
            flash()->error('Une erreur est survenue.');
            return $this->redirectToRoute('app_accueil');
        }

        // Supprimer le panier de l'utilisateur
        $cart = $user->getCart();
        if ($cart) {
            $this->entityManager->remove($cart);
        }

        // Supprimer les commandes associées de l'utilisateur
        foreach ($user->getOrders() as $order) {
            $this->entityManager->remove($order);
        }

        // Supprimer l'utilisateur
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        // Déconnecter l'utilisateur après la suppression du compte
        $this->tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        flash()->success('Votre compte et toutes vos données associées ont été supprimées avec succès.');
        return $this->redirectToRoute('app_accueil');
    }

    #[Route(path: '/api-access-enable', name: 'app_api_access_enable', methods: ['POST'])]
    public function enableApiAccess(Request $request): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        // Vérifie le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('app_api_access_enable', $token)) {
            flash()->error('Une erreur est survenue.');
            return $this->redirectToRoute('app_order_show');
        }

        $roles = $user->getRoles();
        if (in_array('ROLE_API_ACCESS', $roles, true)) {
            flash()->info('Votre accès API est déjà activé.');
            return $this->redirectToRoute('app_order_show');
        }
        $roles[] = 'ROLE_API_ACCESS';
        $user->setRoles($roles);

        $this->entityManager->flush();

        $this->refreshSecurityToken($user);

        flash()->success('Votre accès API a été activé avec succès.');
        return $this->redirectToRoute('app_order_show');
    }

    #[Route(path: '/api-access-disable', name: 'app_api_access_disable', methods: ['POST'])]
    public function disableApiAccess(Request $request): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        // Vérifie le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('app_api_access_disable', $token)) {
            flash()->error('Une erreur est survenue.');
            return $this->redirectToRoute('app_order_show');
        }

        $roles = $user->getRoles();
        if (!in_array('ROLE_API_ACCESS', $roles, true)) {
            flash()->info('Votre accès API est déjà désactivé.');
            return $this->redirectToRoute('app_order_show');
        }
        $roles = array_filter($roles, fn($role) => $role !== 'ROLE_API_ACCESS');
        $user->setRoles($roles);

        $this->refreshSecurityToken($user);

        $this->entityManager->flush();

        flash()->success('Votre accès API a été désactivé avec succès.');
        return $this->redirectToRoute('app_order_show');
    }

    private function refreshSecurityToken(\App\Entity\User $user): void
    {
        $token = new UsernamePasswordToken(
            $user,
            'main',
            $user->getRoles()
        );
        $this->tokenStorage->setToken($token);
    }
}
