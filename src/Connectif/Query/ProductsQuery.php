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
        return 'kpy.query.connectif.products';
    }

    /**
     * @throws KpyInvalidProductCode
     */
    public function fetch(array $params = []): array
    {
        $shop = $params['shop'] ?? 1;
        $lang = $params['language'] ?? 1;

        $filterProduct = '';

        if (isset($params['product_code']) && $params['product_code'] instanceof ProductCode) {
            $productCode = $params['product_code'];

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
            "WITH taxes as (
                SELECT trg.id_tax_rules_group, t.rate
                FROM ps_tax_rules_group trg
                INNER join ps_tax_rule tr on tr.id_tax_rules_group = trg.id_tax_rules_group and tr.id_country = 6
                INNER join ps_tax t on t.id_tax = tr.id_tax
                WHERE trg.active = 1
            )
            SELECT CONCAT_WS('-', ps.id_product, IFNULL(pa.id_product_attribute, 0)) as sku, p.id_product,
            CONCAT_WS(' ', pl.name, al.name) as name, IFNULL(m.name, '-') as brand, cl.link_rewrite as link_categoria,
            pl.link_rewrite as link_producto, IFNULL(pa.id_product_attribute, 0) as id_product_attribute,
            ps.visibility, ps.active as activo, ifnull(kpa.active, 1) as combination_active, sa.quantity,
            IF(EXISTS(SELECT 1 FROM ps_kpy_packs ppa WHERE ppa.id_product_pack = CONCAT_WS('-', ps.id_product, IFNULL(pa.id_product_attribute, 0))), 'si', 'no') as pack,
            if(exists(select 1 from ps_kpy_special_price ksp WHERE now() between ksp.date_from and ksp.expire and ksp.id_shop = ps.id_shop and ksp.id_product=ksp.id_product_attribute), 'si', 'no') as 'special_price',
            IF(EXISTS(SELECT 1 FROM ps_category_product cp WHERE cp.id_product = ps.id_product and cp.id_category=2228), 'si', 'no') as 'pienso_classic_perro',
            IF(EXISTS(SELECT 1 FROM ps_category_product cp WHERE cp.id_product = ps.id_product and cp.id_category=2230), 'si', 'no') as 'pienso_classic_gato',
            ROUND((ps.price+ifnull(pa.price, 0)), 2) as pvp,
            ROUND((ps.price+ifnull(pa.price, 0))*(1+(ifnull(t.rate, 0)/100)), 2) as pvp_tax_included,
            ROUND(1 + t.rate/100, 2) as tax_rate
            FROM ps_product_shop ps
            INNER JOIN ps_product p
                on p.id_product = ps.id_product
            INNER JOIN ps_product_lang pl
                on pl.id_product = ps.id_product and pl.id_shop = ps.id_shop and pl.id_lang = {$lang}
            LEFT JOIN ps_product_attribute_shop pa
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
            LEFT JOIN ps_kpy_product_attribute kpa
                on kpa.id_product_attribute = pa.id_product_attribute
            LEFT JOIN ps_stock_available sa
                ON sa.id_product = p.id_product and sa.id_product_attribute = IFNULL(pa.id_product_attribute, 0)
            LEFT JOIN taxes t
                    on t.id_tax_rules_group = ps.id_tax_rules_group
            WHERE ps.id_shop = {$shop} {$filterProduct}
            GROUP BY ps.id_product, pa.id_product_attribute
            ORDER BY ps.id_product, IFNULL(pa.id_product_attribute, 0)
            "
        );
    }
}
