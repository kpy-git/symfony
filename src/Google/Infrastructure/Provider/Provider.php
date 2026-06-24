<?php

namespace App\Google\Infrastructure\Provider;

use App\Shared\Infrastructure\Database\DatabaseBus;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Shared\Infrastructure\Database\Exception\KpyNotFoundDatabaseException;

class Provider
{
    private static array $topProducts;

    private DatabaseInterface $aquaDatabase;

    private DatabaseInterface $kompyDatabase;

    private DatabaseInterface $doctrineDatabase;

    /**
     * @throws KpyNotFoundDatabaseException
     */
    public function __construct(private readonly DatabaseBus $databaseBus)
    {
        $this->aquaDatabase = $this->databaseBus->getAquaDatabase();
        $this->kompyDatabase = $this->databaseBus->getKompyDatabase();
        $this->doctrineDatabase = $this->databaseBus->getDoctrineDatabase();
    }

    public function infoAqua(): array
    {
        $sql = "SELECT PR.PRODUCTO,  S.STOCK_DISPONIBLE, P.PESO, P.GRUPOLOGISTICO, P.STOCK_SYNC, R.CODIGO AS REFERENCIA,
                        F.NOMBRE AS FABRICANTE, ISNULL(DESCRIPTEC, '') AS DESCRIPTEC,
                        (SELECT TOP 1 E.EAN FROM DATWMREAN01 E WITH(NOLOCK) WHERE E.PRODUCTO=P.CODIGO ORDER BY E.ALTA DESC) AS EAN,
                        ISNULL(PR.PYM, 0) AS PYM, ISNULL(PR.WEC, 0) AS WEC, ISNULL(PR.ITA, 0) AS ITA,
                        CASE WHEN PR.VALOR_MEDIO = 0 THEN PR.COMPRA_CON_DTOS ELSE PR.VALOR_MEDIO END AS COSTE,
                        PR.COSTE_CAJA, PR.COSTE_ENVIO_ES, PR.COSTE_ENVIO_PT, PR.COSTE_ENVIO_IT, PR.LIQUIDACION,
                        CASE WHEN P.TIPOIVA = 3 THEN 1.1 WHEN P.TIPOIVA = 2 THEN 1.21 ELSE 0 END AS IVA_DE_COMPRAS
                FROM DATPYMPRDPRICES01 PR WITH(NOLOCK)
                LEFT JOIN DATIN01 P WITH(NOLOCK) ON PR.PRODUCTO=P.CODIGO
                    -- si ponemos los filtros en el where no saldran los packs
                    AND P.CONTROLADO=1 AND P.DESCATALOGADO=0 AND P.FABRICANTE NOT IN ('108')
                LEFT JOIN PRODUCTSTOCK S ON S.CODIGO = P.CODIGO
                LEFT JOIN DATCAPR01 R WITH(NOLOCK) ON R.CODART=P.CODIGO
                LEFT JOIN DATPYMFABRICANTES01 F WITH(NOLOCK) ON F.CODIGO=P.FABRICANTE";

        $data = $this->aquaDatabase->execute($sql);

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

    public function combinacionesDesactivadas(): array
    {
        $sql = "SELECT id_product_attribute FROM ps_kpy_product_attribute WHERE disabled=1";
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

        $packs = [];
        $results = $this->kompyDatabase->execute($sql);
        foreach ($results as $pack) {
            $packs[] = $pack['id_product_pack'];
        }

        return $packs;
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
                inner join ps_kpy_gitfs r
                    on r.id = rp.id_regalo
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

    public static function getMeasuringUnits()
    {
        $file = fopen($_ENV['APP_SHARE_DIR'] . '/measuring.csv', 'rb');
        $measuring = [];

        if (!$file) {
            return $measuring;
        }
        // salta la cabecera
        fgetcsv($file);

        while (($line = fgets($file)) !== false) {
            [$sku, $unit_pricing_measure, $unit_pricing_base_measure] = explode(';', $line);
            $measuring[$sku] = [
                'unit_pricing_measure' => $unit_pricing_measure . ' ' . $line['medida'],
                'unit_pricing_base_measure' =>  $unit_pricing_base_measure . ' ' . $line['medida'],
            ];
        }

        fclose($file);

        return $measuring;
    }

    public static function getTopProductosFromFile(): array
    {
        if (!empty(self::$topProducts)) {
            return self::$topProducts;
        }

        $file = fopen($_ENV['APP_SHARE_DIR'] . '/top_products.csv', 'rb');

        if (!$file) {
            return [];
        }
        // salta la cabecera
        fgetcsv($file);

        while (($line = fgets($file)) !== false) {
            self::$topProducts [] = trim($line['sku']);
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
}
