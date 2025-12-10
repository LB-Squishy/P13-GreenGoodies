<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
final class ProductApiController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private SerializerInterface $serializer,
        private NormalizerInterface $normalizer,
        private RequestStack $requestStack
    ) {}

    #[Route('/products', name: 'api_products', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        // Récupération des produits actifs
        $products = $this->productRepository->findBy(
            ['isActive' => true],
            ['createdAt' => 'DESC']
        );

        // Normalisation des produits
        $products = $this->normalizer->normalize($products, null, ['groups' => ['getProducts']]);

        // Ajout du base URL aux images des produits
        $productsWithBaseUrl = [];
        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = $request->getSchemeAndHttpHost();
        foreach ($products as $product) {
            if (isset($product['picture'])) {
                $product['picture'] = $baseUrl . '/uploads/products/lg/' . $product['picture'];
            }
            $productsWithBaseUrl[] = $product;
        }

        // Sérialisation en JSON
        $jsonProducts = $this->serializer->serialize($productsWithBaseUrl, 'json');

        // Retour de la réponse JSON
        return new JsonResponse(
            $jsonProducts,
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}
