<?php

namespace App\Connectif\Command;

use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class ProductSynchronizedCommand implements ConnectifCommandInterface
{
    public function __construct(
        #[Autowire(service: 'doctrineDatabase')] private DatabaseInterface $doctrineDatabase,
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.connectif.command.product_synchronized';
    }

    public function execute(array $params = []): bool
    {
        if (!isset($params['product_code']) || !$params['product_code'] instanceof ProductCode) {
            return false;
        }

        $productCode = $params['product_code'];

        return $this->doctrineDatabase->execute(
            "INSERT INTO connectif_product (id_product, id_product_attribute, sync_at)
                VALUES ({$productCode->getProductId()}, {$productCode->getProductAttributeId()}, now())
                ON DUPLICATE KEY UPDATE
                sync_at=NOW()");
    }
}
