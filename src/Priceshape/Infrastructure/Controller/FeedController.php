<?php

namespace App\Priceshape\Infrastructure\Controller;

use App\Shared\Domain\Shop;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/priceshape', host: 'ops.%kpy.base_domain%', name: 'priceshape_')]
class FeedController extends AbstractController
{
    public function __construct(
    )
    {
    }

    #[Route('/feed/{shop}', name: 'feed', methods: ['GET'])]
    public function generateFeed(
        #[Autowire('%kpy.priceshape.var_dir%')] string $feedDir,
        Shop $shop = Shop::KOMPY_ES,
    ): BinaryFileResponse
    {
        $filePath = $feedDir . '/' . $shop->value;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('No hay ningún feed para ' . $shop->value);
        }

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');

        return $response;
    }
}
