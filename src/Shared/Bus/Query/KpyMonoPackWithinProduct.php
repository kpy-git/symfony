<?php

namespace App\Shared\Bus\Query;

use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.shared.query')]
readonly class KpyMonoPackWithinProduct implements KpyQueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.shared.query.monopack_within_product';
    }

    public function fetch(array $params = []): array
    {
        /** @var ProductCode $productCode */
        $productCode = $params['product_code'];

        return $this->kompyDatabase->execute(
            "SELECT pp.id_product_pack, pp.quantity
            FROM ps_kpy_packs pp
            INNER JOIN (
                SELECT pp.id_product_pack
                FROM ps_kpy_packs pp
                WHERE pp.id_product_item = {$productCode->getProductId()}
                  and pp.id_product_attribute_item = {$productCode->getProductAttributeId()}
            ) AS product_packs ON pp.id_product_pack=product_packs.id_product_pack
            GROUP BY id_product_pack
            HAVING COUNT(*) = 1"
        );
    }
}
