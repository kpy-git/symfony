<?php

namespace App\Priceshape\Query;

use App\Priceshape\Domain\ValueObject\Brand;
use App\Priceshape\Infrastructure\Persistence\Doctrine\Model\BrandIncluded;
use Doctrine\ORM\EntityManagerInterface;

readonly class BrandsIncludedQuery implements QueryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function getName(): string
    {
        return 'kpy.priceshape.query.brands_included';
    }

    public function fetch(array $params = []): array
    {
        return array_map(
            static fn (BrandIncluded $brand): Brand => new Brand($brand->getIdManufacturer(), $brand->isFixedPrice()),
            $this->entityManager->getRepository(BrandIncluded::class)->findAll()
        );
    }
}
