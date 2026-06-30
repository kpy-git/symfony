<?php

namespace App\Shared\Bus\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AutoconfigureTag('kpy.shared.query')]
readonly class KpyProductImagesQuery implements KpyQueryInterface
{
    public function __construct(#[Autowire(service: 'kompyDatabase')] private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.query.shared.product_images';
    }

    public function fetch(array $params = []): array
    {
        if ($params['only_first_image'] ?? false) {
            return $this->kompyDatabase->execute(
                "with ranked_images as (
                SELECT id_product, id_image, `position`, row_number () over (partition by id_product order by `position`) as `rank`
                FROM ps_image
                ORDER BY id_product
            )
            select id_product, id_image
            from ranked_images
            where `rank` = 1"
            );
        }

        return $this->kompyDatabase->execute(
            "SELECT id_product, id_image, `position`
                FROM ps_image
                ORDER BY id_product, `position`"
        );
    }
}
