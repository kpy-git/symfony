<?php

namespace App\Priceshape\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class PrestaShopProductsQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.priceshape.query.prestashop_products';
    }

    public function fetch(array $params = []): array
    {
        $shop = $params['shop'] ?? 1;

        $brandsBanned = '';
        if (isset($params['brands_banned']) && is_array($params['brands_banned'])) {
            $brandsBanned = ' AND p.id_manufacturer NOT IN (' . implode(',', $params['brands_banned']) . ') ';
        }

        $sql = "
        WITH taxes as (
            SELECT trg.id_tax_rules_group, t.rate
            FROM ps_tax_rules_group trg
            INNER join ps_tax_rule tr on tr.id_tax_rules_group = trg.id_tax_rules_group and tr.id_country = 6
            INNER join ps_tax t on t.id_tax = tr.id_tax
            WHERE trg.active = 1)
        SELECT p.id_product, IFNULL(pa.id_product_attribute,0) as id_product_attribute, p.id_category_default as category_default,
            p.id_manufacturer, m.name as fabricante,
            CONCAT_WS(' - ', pl.name, (GROUP_CONCAT(DISTINCT CONCAT_WS(': ', agl.name, al.name) ORDER BY ag.position SEPARATOR ' '))) as name,
            cl.link_rewrite as category_rewrite, pl.link_rewrite as product_rewrite, cl.name as name_category_default,
            CONCAT_WS('-', p.id_product, IFNULL(pa.id_product_attribute,0)) as sku, t.rate as iva,
            (select IF(id_feature_value = 1001, 'Gato', 'Perro')
                from ps_feature_product
                where id_feature=14 and id_product=p.id_product
                limit 1) as mascota,
            IF(tag_free_shipping.id_product IS NULL, 'no', 'yes') AS free_shipping,
            IF(EXISTS (SELECT 1 FROM ps_category_product WHERE id_category IN (1069, 1228) and id_product=p.id_product), 'si', 'no') AS antiparasitario,
            IFNULL(sp.special_discount, 0) as special_discount,
            ps.price + ifnull(pa.price, 0) as pvr
        FROM ps_product p
        inner join ps_product_shop ps on ps.id_product = p.id_product and ps.id_shop = {$shop} and ps.active = 1 and ps.visibility = 'both'
        inner JOIN ps_product_lang pl ON pl.id_product=ps.id_product and pl.id_lang = 1 and pl.id_shop = {$shop}
        left join ps_product_attribute_shop pa ON pa.id_product=p.id_product and pa.id_shop = ps.id_shop
        left join ps_kpy_product_attribute kpa ON kpa.id_product_attribute = pa.id_product_attribute
        left join ps_product_attribute_combination pac on pac.id_product_attribute=pa.id_product_attribute
        LEFT JOIN ps_attribute_lang al on al.id_attribute = pac.id_attribute and al.id_lang = pl.id_lang
        LEFT JOIN ps_attribute pat on pat.id_attribute = pac.id_attribute
        LEFT JOIN ps_attribute_group ag on ag.id_attribute_group = pat.id_attribute_group
        LEFT JOIN ps_attribute_group_lang agl on agl.id_attribute_group = pat.id_attribute_group and agl.id_lang = pl.id_lang
        LEFT JOIN ps_manufacturer m ON m.id_manufacturer=p.id_manufacturer
        LEFT JOIN ps_category_lang cl ON cl.id_category = ps.id_category_default AND cl.id_lang = pl.id_lang and cl.id_shop = pl.id_shop
        INNER JOIN taxes t on t.id_tax_rules_group = ps.id_tax_rules_group
        LEFT JOIN (
            SELECT DISTINCT id_product, id_product_attribute
                FROM ps_kpy_product_flag tg
                WHERE tg.id_flag = 6 AND tg.active = 1) AS tag_free_shipping
                ON tag_free_shipping.id_product = ps.id_product AND tag_free_shipping.id_product_attribute = pa.id_product_attribute
        LEFT JOIN ps_kpy_special_price sp
            ON sp.id_product = p.id_product and sp.id_product_attribute = pa.id_product_attribute and sp.id_shop={$shop}
        WHERE
            EXISTS (SELECT 1 FROM ps_neftys_stock ns where ns.id_product=p.id_product and ns.id_product_attribute=ifnull(pa.id_product_attribute, 0))
            AND NOT EXISTS (select 1 FROM ps_category_product cp WHERE cp.id_product = p.id_product AND cp.id_category IN (2292, 586))
            AND IF (kpa.id_product_attribute IS NOT NULL, kpa.active = 1, 1=1)
            {$brandsBanned}
        GROUP BY p.id_product, pa.id_product_attribute
        ORDER BY p.id_product, pa.id_product_attribute";

        return $this->kompyDatabase->execute($sql);
    }
}
