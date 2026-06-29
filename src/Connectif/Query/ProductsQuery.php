<?php

namespace App\Connectif\Query;

use App\Shared\Domain\Exception\KpyInvalidProductCode;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class ProductsQuery implements ConnectifQueryInterface
{
    public function __construct(#[Autowire(service: 'kompyDatabase')] private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.connectif.query.products';
    }

    /**
     * @throws KpyInvalidProductCode
     */
    public function fetch(array $params = []): array
    {
        $shop = $params['shop'] ?? 1;
        $lang = $params['language'] ?? 1;

        $filterProduct = '';

        if (isset($params['sku'])) {
            $productCode = ProductCode::fromSKU($params['sku']);

            $filterProduct = $productCode->isCombinationProduct()
                ? ' AND pa.id_product_attribute = ' . $productCode->getProductAttributeId()
                : ' AND p.id_product = ' . $productCode->getProductId();
        }

        if (isset($params['category'])) {
            $filterProduct .= ' AND EXISTS (SELECT *  FROM ps_category_product cp WHERE cp.id_category = ' . $params['category'] . ' AND cp.id_product = p.id_product) ';
        }

        if (isset($params['manufacturer'])) {
            $filterProduct .= ' AND p.id_manufacturer = ' . $params['manufacturer'];
        }

        return $this->kompyDatabase->execute(
            "SELECT CONCAT_WS('-', ps.id_product, IFNULL(pa.id_product_attribute, 0)) as SKU, p.id_product,
            CONCAT_WS(' ', pl.name, al.name) as name, IFNULL(m.name, '-') as brand, cl.link_rewrite as link_categoria,
            pl.link_rewrite as link_producto, IFNULL(pa.id_product_attribute, 0) as attr,
            ps.visibility, ps.active as activo, IFNULL(pa.disabled, 0) as attr_desactivado,
            FROM ps_product_shop ps
            INNER JOIN ps_product p
                on p.id_product = ps.id_product
            INNER JOIN ps_product_lang pl
                on pl.id_product = ps.id_product and pl.id_shop = ps.id_shop and pl.id_lang = {$lang}
            LEFT JOIN ps_product_attribute pa
                on pa.id_product = ps.id_product
            LEFT JOIN ps_product_attribute_combination pac
                on pac.id_product_attribute = pa.id_product_attribute
            LEFT JOIN ps_attribute_lang al
                ON al.id_attribute = pac.id_attribute AND pl.id_lang = al.id_lang
            LEFT JOIN ps_manufacturer m
                on m.id_manufacturer = p.id_manufacturer
            INNER JOIN ps_category_lang cl
                ON cl.id_category = p.id_category_default AND cl.id_shop = ps.id_shop
                AND cl.id_lang = pl.id_lang
            WHERE ps.id_shop = {$shop} {$filterProduct}
            GROUP BY ps.id_product, pa.id_product_attribute
            ORDER BY ps.id_product, IFNULL(pa.id_product_attribute, 0)"
        );
    }
}
