<?php

namespace App\Shared\Bus\Query;

use App\Connectif\Query\ConnectifQueryInterface;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AutoconfigureTag('kpy.shared.query')]
class CombinationImageQuery implements ConnectifQueryInterface
{
    public function __construct(#[Autowire(service: 'kompyDatabase')] private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.query.shared.combination_image';
    }

    public function fetch(array $params = []): int
    {
        if (!isset($params['id_product_attribute'])) {
            return 0;
        }

        $lang = $params['id_lang'] ?? 1;

        return (int)$this->kompyDatabase->getValue('
            SELECT pai.`id_image`, pai.`id_product_attribute`, il.`legend`
            FROM `ps_product_attribute_image` pai
            LEFT JOIN `ps_image_lang` il ON (il.`id_image` = pai.`id_image`)
            LEFT JOIN `ps_image` i ON (i.`id_image` = pai.`id_image`)
            WHERE pai.`id_product_attribute` = ' . (int)$params['id_product_attribute'] . '
                AND il.`id_lang` = ' . $lang . '
            ORDER by i.`position` LIMIT 1');
    }
}
