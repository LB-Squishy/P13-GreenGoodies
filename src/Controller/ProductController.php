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

    #[Route('/product/{id}', name: 'app_product_show')]
    public function showProduct(?Product $product): Response
    {
        if (!$product || !$product->isActive()) {
            throw $this->createNotFoundException('Le produit demandé n\'existe pas ou n\'est plus disponible. Passer la valeur is_active à 1 dans la table product pour le rendre actif ou créez le produit.');
        }
        return $this->render('product/product.html.twig', [
            'product' => $product,
        ]);
    }
}
