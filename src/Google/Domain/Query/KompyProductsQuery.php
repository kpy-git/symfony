<?php

namespace App\Google\Domain\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class KompyProductsQuery implements QueryInterface
{
    public function __construct(
        #[Autowire(service: 'kompyDatabase')] private DatabaseInterface $kompyDatabase
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.query.google.kompy_products';
    }

    public function fetch(array $params = []): array
    {
        $shopId = $params['shop'] ?? 1;
        $langId = $params['language'] ?? 1;

        return $this->kompyDatabase->execute(
            "WITH taxes as (
            SELECT trg.id_tax_rules_group, t.rate
            FROM ps_tax_rules_group trg
            INNER join ps_tax_rule tr on tr.id_tax_rules_group = trg.id_tax_rules_group and tr.id_country = 6
            INNER join ps_tax t on t.id_tax = tr.id_tax
            WHERE trg.active = 1)
            SELECT p.id_product, IFNULL(pa.id_product_attribute,0) as attr, p.id_category_default as category_default,
                    p.id_manufacturer, m.name as fabricante, pl.description_short AS description, pl.name as name,
                    cl.link_rewrite as category_rewrite, pl.link_rewrite as product_rewrite, cl.name as name_category_default,
                    CONCAT_WS('-', p.id_product, IFNULL(pa.id_product_attribute,0)) as sku, ifnull(t.rate, 0) as iva,
                    (select case when id_feature_value = 1001 then 'Gato' else 'Perro' end
                        from ps_feature_product
                        where id_feature=14 and id_product=p.id_product
                        limit 1) as mascota,
                    CASE WHEN tag_free_shipping.id_product IS NULL THEN 'no' ELSE 'yes' END AS free_shipping,
                    ROUND((ps.price+pas.price)*(1+(ifnull(t.rate, 0)/100)), 2) as pvp,
                    IF(EXISTS(select * FROM ps_category_product WHERE id_product=p.id_product AND id_category=2292), 'si', 'no') as OUTLET
                FROM ps_product p
                inner join ps_product_shop ps
                    on ps.id_product = p.id_product and ps.id_shop = {$shopId} and ps.active = 1 and ps.visibility = 'both'
                inner JOIN ps_product_lang pl
                    ON pl.id_product=ps.id_product and pl.id_lang = {$langId} and pl.id_shop = ps.id_shop
                left join ps_product_attribute pa
                    ON pa.id_product=p.id_product
                LEFT JOIN ps_manufacturer m
                    ON m.id_manufacturer=p.id_manufacturer
                LEFT JOIN ps_category_lang cl
                    ON cl.id_category = ps.id_category_default AND cl.id_lang = {$langId} and cl.id_shop = ps.id_shop
                left join ps_product_attribute_shop pas
                    ON pas.id_product_attribute = pa.id_product_attribute and pas.id_shop = ps.id_shop
                LEFT JOIN taxes t
                    on t.id_tax_rules_group = ps.id_tax_rules_group
                LEFT JOIN (
                    SELECT DISTINCT id_product, id_product_attribute
                        FROM ps_kpy_product_flag tg
                        WHERE tg.id_flag = 6 AND tg.active = 1) AS tag_free_shipping
                        ON tag_free_shipping.id_product = ps.id_product AND tag_free_shipping.id_product_attribute = pa.id_product_attribute
                WHERE NOT EXISTS (select * FROM ps_category_product WHERE id_product=p.id_product AND id_category IN (1509, 586))
                ORDER BY p.id_product, pa.id_product_attribute"
        );

    }
}
