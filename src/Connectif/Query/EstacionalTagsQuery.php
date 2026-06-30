<?php

namespace App\Connectif\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class EstacionalTagsQuery implements ConnectifQueryInterface
{
    public function __construct(
        #[Autowire(service: 'doctrineDatabase')] private DatabaseInterface $database,
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.query.connectif.estacional_tags';
    }

    public function fetch(array $params = []): array
    {
        return $this->database->execute(
            "select id_product, cast(json_extract(extra_tags, '$.estacional') as char) as tag_estacional
                from connectif_product
                where json_extract(extra_tags, '$.estacional') is not null"
        );
    }
}
