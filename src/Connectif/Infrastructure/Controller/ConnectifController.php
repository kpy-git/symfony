<?php

namespace App\Connectif\Infrastructure\Controller;

use App\Shared\Domain\Service\CategoriesBreadcrumbGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/connectif', host: 'ops.%kpy.base_domain%', name: 'connectif_')]
final class ConnectifController extends AbstractController
{
    #[Route('/products', name: 'sync_products', methods: ['GET'])]
    public function index(CategoriesBreadcrumbGenerator $categoriesBreadcrumbGenerator): JsonResponse
    {
        return $this->json([
            'categories' => $categoriesBreadcrumbGenerator->getAllCategoriesBreadcrumbByProduct(214),
        ]);
    }
}
