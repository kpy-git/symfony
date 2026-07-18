<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Domain\Exception\KpyProductNotFoundException;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @throws KpyProductNotFoundException
     */
    public function findOrFail(ProductCode $productCode): Product
    {
        $product = $this->findOneBy([
            'productId' => $productCode->getProductId(),
            'productAttributeId' => $productCode->getProductAttributeId(),
        ]);

        if (!$product) {
            throw new KpyProductNotFoundException('No existe ningún producto con el sku ' . $productCode->getSku());
        }

        return $product;
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
