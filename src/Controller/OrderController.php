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
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class OrderController extends AbstractController
{
    public function __construct(
        private CartItemRepository $cartItemRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route('/compte', name: 'app_order_show')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        $order = $user->getOrders();

        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
            'order' => $order,
        ]);
    }
}
