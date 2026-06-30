<?php

namespace App\Connectif\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class RelatedProductsQuery implements ConnectifQueryInterface
{
    public function __construct(
        #[Autowire(service: 'doctrineDatabase')] private DatabaseInterface $database,
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.query.connectif.related_products';
    }

    public function fetch(array $params = []): array
    {
        return $this->database->execute(
            "SELECT id_product, CONCAT_WS('-', id_product_related, id_product_attribute_related) as related
            FROM connectif_product_related"
        );
    }
}
