<?php

namespace App\Shared\Infrastructure\API;

use App\Shared\Bus\Query\KpyQueryBus;
use App\Shared\Domain\Shop;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

readonly class KpyPublicApi implements KpyPublicApiInterface
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private KpyQueryBus $queryBus)
    {
    }

    public function getProductWeight(ProductCode $productCode): float
    {
        /** @var Product $product */
        $product = $this->entityManager->getRepository(Product::class)->findOrFail($productCode);

        return $product->getWeight();
    }

    public function getAllProductsWeights(): array
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return array_reduce($products, static function (array $carry, Product $product) {
            $carry[ProductCode::from($product->getProductId(), $product->getProductAttributeId())->getSku()] = $product->getWeight();
            return $carry;
        }, []);
    }

    public function getProductBrandId(ProductCode $productCode): int
    {
        /** @var Product $product */
        $product = $this->entityManager->getRepository(Product::class)->findOrFail($productCode);

        return $product->getBrandId();
    }

    public function getProduct(ProductCode $productCode): \App\Shared\Domain\ValueObject\Product
    {
        /** @var Product $product */
        $product = $this->entityManager->getRepository(Product::class)->findOrFail($productCode);

        return $this->createProductFromEntity($product);
    }

    private function createProductFromEntity(Product $product): \App\Shared\Domain\ValueObject\Product
    {
        return new \App\Shared\Domain\ValueObject\Product(
            ProductCode::from($product->getProductId(), $product->getProductAttributeId()),
            $product->getWeight(),
            $product->getBrandId(),
            $product->getSalesPriceES(),
            $product->isPack(),
            $product->isJirafa(),
        );
    }


    public function getAllProductsBySKU(): array
    {
        $entities = $this->entityManager->getRepository(Product::class)->findAll();

        return array_reduce(
            $entities,
            function (array $carry, Product $entity) {
                $product = $this->createProductFromEntity($entity);
                $carry[$product->getProductCode()->getSku()] = $product;
                return $carry;
            }, []
        );
    }

    public function getAllFirstImagesIndexByProduct(): array
    {
        return array_reduce(
            $this->queryBus->fetch('kpy.shared.query.product_images', ['only_first_image' => true]),
            static function (array $carry, array $row): array {
                $carry[$row['id_product']] = $row['id_image'];
                return $carry;
            }, []
        );
    }

    public function getAllImagesIndexByProduct(): array
    {
        return array_reduce(
            $this->queryBus->fetch('kpy.shared.query.product_images'),
            static function (array $carry, array $row): array {
                $carry[$row['id_product']][] = $row['id_image'];
                return $carry;
            }, []
        );
    }

    public function getProductSalesPricesByShop(Shop $shop): array
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return array_reduce($products, static function (array $carry, Product $product) {
            $carry[ProductCode::from($product->getProductId(), $product->getProductAttributeId())->getSku()] = $product->getSalesPriceES();
            return $carry;
        }, []);
    }
}
