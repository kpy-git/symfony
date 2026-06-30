<?php

namespace App\Connectif\Command;

use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class ProductSynchronizedBatchCommand implements ConnectifCommandInterface
{
    public function __construct(
        #[Autowire(service: 'doctrineDatabase')] private DatabaseInterface $doctrineDatabase,
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.connectif.command.product_synchronized_batch';
    }

    public function execute(array $params = []): bool
    {
        if (!isset($params['product_code']) || !$params['product_code'] instanceof ProductCode) {
            return false;
        }

        $productCode = $params['productCode'];

        return $this->doctrineDatabase->execute(
            "UPDATE connectif_product
                SET sync_at=NOW()
                WHERE id_product={$productCode->getProductId()}
                  and id_product_attribyute={$productCode->getProductAttributeId()}");
    }
}
