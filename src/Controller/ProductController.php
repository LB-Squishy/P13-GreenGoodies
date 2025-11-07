<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
    ) {}

    #[Route('/', name: 'app_accueil')]
    public function index(): Response
    {
        $products = $this->productRepository->findBy(['isActive' => true], ['createdAt' => 'DESC'], 9);

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }
}
