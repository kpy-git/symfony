<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class OrderProductsQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.order_products';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->execute(
            "with orderlines as (
            select if(kp.id_product_item is null, od.product_quantity, od.product_quantity*kp.quantity) as `quantity`,
                   if(kp.id_product_item is null, od.product_id, kp.id_product_item) as `id_product`,
                   if(kp.id_product_item is null, od.product_attribute_id, kp.id_product_attribute_item) as `id_product_attribute`
            from ps_orders o
            inner join ps_order_detail od
                on od.id_order = o.id_order
            left join ps_product_attribute pa
                on pa.id_product_attribute = od.product_attribute_id
            left join (
                select kp.id_product_pack, kp.id_product_item, kp.id_product_attribute_item, kp.quantity
                from ps_kpy_packs kp
                /* para evitar sacar los packs que se crean para regalar un producto que vendemos normalmente */
                where NOT EXISTS (SELECT 1 FROM ps_kpy_gifts r WHERE r.id_product_attribute = SUBSTRING_INDEX(kp.id_product_pack, '-', -1))
                group by kp.id_product_pack
                having count(*) = 1
                ) kp on kp.id_product_pack = CONCAT_WS('-', od.product_id, od.product_attribute_id)
            where o.id_order = {$params['id_order']})
            select orderlines.id_product,
                   orderlines.id_product_attribute,
                   orderlines.quantity,
                   if(pa.id_product_attribute = 0, p.ean13, pa.ean13) as `ean`,
                   CONCAT_WS(' - ', pl.name, (GROUP_CONCAT(DISTINCT CONCAT_WS(': ', agl.name, al.name) ORDER BY ag.position SEPARATOR ' '))) as `name`,
                   sum(p.weight + ifnull(pa.weight, 0)) as `weight`
            from ps_product p
            inner join orderlines
                on orderlines.id_product = p.id_product
            inner join ps_product_lang pl
                on pl.id_product = orderlines.id_product and pl.id_shop = 1 and pl.id_lang = 1
            left join ps_product_attribute pa
                on pa.id_product_attribute = orderlines.id_product_attribute
            left join ps_product_attribute_combination pac
                on pac.id_product_attribute = pa.id_product_attribute
            left join ps_attribute_lang al
                on al.id_attribute = pac.id_attribute and al.id_lang = pl.id_lang
            LEFT JOIN ps_attribute pat
                on pat.id_attribute = pac.id_attribute
            LEFT JOIN ps_attribute_group ag
                on ag.id_attribute_group = pat.id_attribute_group
            LEFT JOIN ps_attribute_group_lang agl
                on agl.id_attribute_group = pat.id_attribute_group and agl.id_lang = pl.id_lang
            group by p.id_product, pa.id_product_attribute"
        );
    }
}
