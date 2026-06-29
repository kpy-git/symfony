<?php

namespace App\Google\Infrastructure\Provider;

use App\Google\Domain\Query\QueryBus;
use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Shared\Domain\Shop;
use App\Shared\Infrastructure\Database\DatabaseBus;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Shared\Infrastructure\Database\Exception\KpyNotFoundDatabaseException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class Provider
{
    private static array $topProducts;

    private DatabaseInterface $aquaDatabase;

    private DatabaseInterface $kompyDatabase;

    private DatabaseInterface $doctrineDatabase;



    /**
     * @throws KpyNotFoundDatabaseException
     */
    public function __construct(
        private readonly DatabaseBus $databaseBus,
        private readonly QueryBus $queryBus,
        #[Autowire('%kpy.google_dir%')]
        private readonly string $googleDir
    )
    {
        $this->aquaDatabase = $this->databaseBus->getAquaDatabase();
        $this->kompyDatabase = $this->databaseBus->getKompyDatabase();
        $this->doctrineDatabase = $this->databaseBus->getDoctrineDatabase();
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function infoAqua(): array
    {
        $data = $this->queryBus->fetch('kpy.query.google.info.aqua');

        $productos = array();

        foreach ($data as $producto) {
            $key = trim($producto['PRODUCTO']);

            $productos[$key]['peso'] = $producto['PESO'];
            $productos[$key]['ean'] = $producto['EAN'];
            $productos[$key]['referencia'] = $producto['REFERENCIA'];
            $productos[$key]['stock'] = $producto['STOCK_DISPONIBLE'];
            $productos[$key]['sync'] = $producto['STOCK_SYNC'];
            $productos[$key]['grupoLogistico'] = $producto['GRUPOLOGISTICO'];
            $productos[$key]['fabricante'] = $producto['FABRICANTE'];
            $productos[$key]['nombre'] = $producto['DESCRIPTEC'];
            $productos[$key]['costeConIva'] = (float)$producto['COSTE'];
            $productos[$key]['ivaParaCompras'] = (float)$producto['IVA_DE_COMPRAS'];
            $productos[$key]['caja'] = (float)$producto['COSTE_CAJA'];
            $productos[$key]['envioES'] = (float)$producto['COSTE_ENVIO_ES'];
            $productos[$key]['envioPT'] = (float)$producto['COSTE_ENVIO_PT'];
            $productos[$key]['envioIT'] = (float)$producto['COSTE_ENVIO_IT'];
            $productos[$key]['liquidacion'] = $producto['LIQUIDACION'];
            $productos[$key]['PYM'] = (float)$producto['PYM'];
            $productos[$key]['ITA'] = (float)$producto['ITA'];
            $productos[$key]['WEC'] = (float)$producto['WEC'];
        }

        return $productos;
    }

    public function getProductosDesdePS(Shop $shop): array
    {
        return $this->queryBus->fetch('kpy.query.google.kompy_products', [
            'shop' => $shop->getId(),
            'language' => $shop->getLanguageId(),
        ]);
    }

    public function productosProhibidos(int $shop): array
    {
        $columna = 'GS_ESP_BANNED';
        if ($shop == 2) {
            $columna = 'GS_PT_BANNED';
        } else if ($shop == 3) {
            $columna = 'GS_IT_BANNED';
        }

        $sql = "SELECT RTRIM(PRODUCTO) AS SKU FROM DATPYMPRDPRICES01 WITH(NOLOCK) WHERE {$columna}=1";
        $results = $this->aquaDatabase->execute($sql);
        $prohibidos = array();

        if (!empty($results)) {
            foreach ($results as $producto) {
                $prohibidos[] = $producto['SKU'];
            }
        }

        return $prohibidos;
    }

    public function marcasProhibidas(int $shop): array
    {
        $columna = '';
        switch ($shop) {
            case 1:
                $columna = 'GS_BANNED_ESP';
                break;
            case 2:
                $columna = 'GS_BANNED_PT';
                break;
            case 3:
                $columna = 'GS_BANNED_IT';
                break;
            default:
                $columna = 'GS_BANNED_ESP';
                break;
        }

        $marcas  = array();
        $results = $this->aquaDatabase->execute("SELECT CODIGO FROM DATPYMFABRICANTES01 WITH(NOLOCK) WHERE {$columna}=1");

        if (!empty($results)) {
            foreach ($results as $marca) {
                $marcas[] = trim($marca['CODIGO']);
            }
        }

        return $marcas;
    }

    public function combinacionesDesactivadas(): array
    {
        $sql = "SELECT id_product_attribute FROM ps_kpy_product_attribute WHERE active=0";
        $data = $this->kompyDatabase->execute($sql);
        $results = array();

        if (!empty($data)) {
            foreach ($data as $combinacion) {
                $results[] = $combinacion['id_product_attribute'];
            }
        }

        return $results;
    }

    public function cargaImagenes(): array
    {
        $sql = "select id_product, GROUP_CONCAT(id_image ORDER BY `position` SEPARATOR ',') as images
                from ps_image
                group by id_product";

        $imagenes = array();
        $data = $this->kompyDatabase->execute($sql);

        foreach ($data as $product) {
            $imagenes[$product['id_product']] = explode(',', $product['images']);
        }

        return $imagenes;
    }

    public function getPacks(): array
    {
        $sql = "SELECT id_product_pack, id_product_item, id_product_attribute_item, quantity
                    FROM ps_kpy_packs pp
                    where exists (select * from ps_product_attribute where id_product_attribute = SUBSTRING_INDEX(pp.id_product_pack, '-', -1))
                      /* para evitar sacar los packs que se crean para regalar un producto que vendemos normalmente */
                    AND NOT EXISTS (SELECT * FROM ps_kpy_gifts r WHERE r.id_product_attribute = SUBSTRING_INDEX(pp.id_product_pack, '-', -1))
                    GROUP BY id_product_pack
                    having count(*) = 1";

        $data  = $this->kompyDatabase->execute($sql);
        $packs = array();

        foreach ($data as $pack) {
            $packs[$pack['id_product_item'] . "-" . $pack['id_product_attribute_item']]['id_pack']     = $pack['id_product_pack'];
            $packs[$pack['id_product_item'] . "-" . $pack['id_product_attribute_item']]['quantity'] = $pack['quantity'];
        }

        return $packs;
    }

    public function getArrayIdsPacks(): array
    {
        $sql = "SELECT id_product_pack
                    FROM ps_kpy_packs
                    GROUP BY id_product_pack
                    having count(*) = 1";

        return array_map(static fn (array $row): string => $row['id_product_pack'], $this->kompyDatabase->execute($sql));

    }

    public  function getProductosAntiparasitarios(): array
    {
        $sql = 'SELECT id_product FROM ps_category_product WHERE id_category IN (1069, 1228)';

        $results = $this->kompyDatabase->execute($sql);
        $productos = [];

        foreach ($results as $producto) {
            $productos[] = $producto['id_product'];
        }

        return $productos;
    }

    public function getProductosConPrecioEspecial(int $shop): array
    {
        $sql = "SELECT CONCAT_WS('-', id_product, id_product_attribute) as sku, special_discount, old_discount
                FROM ps_kpy_special_price WHERE id_shop={$shop}";

        $results = $this->kompyDatabase->execute($sql);
        $productos = [];

        if (is_array($results)) {
            foreach ($results as $producto) {
                $productos[$producto['sku']] = [
                    'special_discount' => $producto['special_discount'],
                    'old_discount' => $producto['old_discount']
                ];
            }
        }

        return $productos;
    }

    public function getProductosConRegalo(int $shop, int $lang): array
    {
        $sql = "SELECT CONCAT_WS('-', rp.id_product, rp.id_product_attribute) as sku, pl.name as regalo
                from ps_kpy_gift_product rp
                inner join ps_kpy_gifts r
                    on r.id_gift = rp.id_gift
                inner join ps_product_lang pl
                    on pl.id_product = r.id_product and pl.id_lang={$lang} and pl.id_shop={$shop}
                WHERE exists (select 1 FROM ps_kpy_gift_shop gs WHERE gs.id_gift=r.id_gift and gs.id_shop=pl.id_shop and gs.active=1)";

        $results = $this->kompyDatabase->execute($sql);
        $productos = [];

        if (is_array($results)) {
            foreach ($results as $producto) {
                $productos[$producto['sku']] = $producto['regalo'];
            }
        }

        return $productos;
    }

    public function getNamesFeed(int $shop): array
    {
        /* TODO - El nombre del producto en el feed lo tiene que sacar del repositorio */
        return [];

        $namesFeed  = array();
        $results = $this->kompyDatabase->execute("SELECT * FROM ps_pym_product_name_gshopping WHERE id_shop = {$shop}");

        if (!empty($results)) {
            foreach ($results as $result) {
                $namesFeed[$result['id_product'].'-'.$result['id_product_attribute']] = trim($result['name']);
            }
        }

        return $namesFeed;
    }

    public function getImagenesPersonalizadas(int $shop): array
    {
        /**
         * TODO - las imágenes se tienen que sacar del repositorio de Doctrine
         */
        return [];
        $imagenesPersonalizadas  = array();
        $results = $this->kompyDatabase->execute("SELECT * FROM ps_pym_imagenes_feed WHERE shop = {$shop}");

        if (!empty($results)) {
            foreach ($results as $result) {
                $imagenesPersonalizadas[$result['id_product'].'-'.$result['id_product_attribute']] = trim($result['ruta_imagen']);
            }
        }

        return $imagenesPersonalizadas;
    }

    public function getProductosEnRoturaSinStock(): array
    {
        return [];
        $sql = "SELECT CONCAT_WS('-', id_product, id_product_attribute) as sku
                FROM ps_kpy_roturas
                WHERE activado=1 AND stock<=0";
        $results = $this->kompyDatabase->execute($sql);
        $productos = [];

        if(!empty($results)) {
            foreach ($results as $producto) {
                $productos[] = $producto['sku'];
            }
        }

        return $productos;
    }

    public function getMeasuringUnits(): array
    {
        $file = fopen($this->googleDir . '/measuring.csv', 'rb');
        $measuring = [];

        if (!$file) {
            return $measuring;
        }
        // salta la cabecera
        fgetcsv($file);

        while (($line = fgets($file)) !== false) {
            [$sku, $medida, $unit_pricing_measure, $unit_pricing_base_measure] = explode(';', $line);
            $measuring[$sku] = [
                'unit_pricing_measure' => $unit_pricing_measure . ' ' . $medida,
                'unit_pricing_base_measure' =>  $unit_pricing_base_measure . ' ' . $medida,
            ];
        }

        fclose($file);

        return $measuring;
    }

    public function getTopProducts(): array
    {
        if (!empty(self::$topProducts)) {
            return self::$topProducts;
        }

        $file = fopen($this->googleDir . '/top_products.csv', 'rb');

        if (!$file) {
            return [];
        }

        while (($line = fgets($file)) !== false) {
            if (trim($line) === '' || !str_contains($line, '-')) {
                continue;
            }
            self::$topProducts [] = trim($line);
        }

        return self::$topProducts;
    }

    public function getPriceShapeInfo(string $country): array
    {
        $results = $this->doctrineDatabase->execute("SELECT CONCAT_WS('-', ppi.id_product, ppi.id_product_attribute) sku,
                ppi.matches, ppi.range_position,
                if(exists(select *
                    from priceshape_product_tags ppt
                    where ppt.id_product=ppi.id_product
                        and ppt.id_product_attribute=ppi.id_product_attribute
                        and ppt.country=ppi.country and ppt.tag='Caros'), 'yes', 'no') as caro
            FROM priceshape_product_info ppi
            WHERE ppi.country='{$country}'");

        return array_reduce($results, static function (array $carry, array $row) {
            $carry[$row['sku']] = [
                'matches' => $row['matches'],
                'position' => $row['range_position'],
                'caro' => $row['caro'],
            ];
            return $carry;
        }, []);
    }

    public function getBrandsWithStockSync(): array
    {
        return array_map(static function (array $brand) {
            return $brand['CODIGO'];
        }, $this->aquaDatabase->execute("SELECT RTRIM(CODIGO) AS CODIGO
                FROM DATPYMFABRICANTES01 WITH(NOLOCK)
                WHERE GS_STOCK_REAL=1")
        );
    }

    public function esBlackFriday(): bool
    {
        return false;
    }

    public function esAniversario(): bool
    {
        return false;
    }

    public function combinacionesMayoresFormatosPienso(): array
    {
        return array_map(static fn (array $row): int => (int)$row['id_product_attribute'], $this->kompyDatabase->execute(
            "WITH ranked_combinations AS (
                    SELECT combinations.id_product,
                           combinations.id_product_attribute,
                           combinations.peso,
                           row_number() over (partition by combinations.id_product order by combinations.peso desc) as posicion
                    FROM (SELECT p.id_product, pa.id_product_attribute, (pa.weight + p.weight) as peso
                           FROM ps_product_attribute pa
                                    LEFT JOIN ps_product p ON p.id_product = pa.id_product
                           WHERE NOT EXISTS (SELECT 1
                                             FROM ps_kpy_product_attribute kpa
                                             WHERE pa.id_product_attribute = kpa.id_product_attribute
                                               AND active = 0)
                             AND NOT EXISTS (SELECT 1
                                             FROM ps_kpy_packs kpp
                                             WHERE kpp.id_product_pack = CONCAT_WS('-', p.id_product, pa.id_product_attribute))
                           ) AS combinations
                )
                SELECT id_product_attribute
                FROM ranked_combinations
                WHERE posicion = 1"
        ));
    }

    public function ProductsWithAlternativeSku(): array
    {
        return $this->queryBus->fetch('kpy.query.google.products_with_alternative_skus');
    }
}
