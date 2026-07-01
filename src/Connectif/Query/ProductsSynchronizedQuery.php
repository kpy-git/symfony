<?php

namespace App\Connectif\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class ProductsSynchronizedQuery implements ConnectifQueryInterface
{
    public function __construct(
        #[Autowire(service: 'doctrineDatabase')] private DatabaseInterface $doctrineDatabase,
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.connectif.query.products_synchronized';
    }

    public function fetch(array $params = []): array
    {
        return $this->doctrineDatabase->execute(
            "SELECT CONCAT_WS('-', id_product, id_product_attribute) as sku
                FROM connectif_product
                WHERE sync_at is not null"
        );
    }
}
