<?php

namespace App\Google\Service;

use App\Google\Domain\Command\CommandBus;
use App\Google\Domain\Exception\KpyGoogleException;
use App\Google\Domain\GoogleDebugMode;
use App\Google\Domain\GoogleMerchantFeed;
use App\Google\Infrastructure\Provider\Provider;
use App\Shared\Bus\Command\KpyCommandNotFoundException;
use App\Shared\Domain\Destination;
use App\Shared\Domain\Service\SFTPFileUploader;
use App\Shared\Domain\Service\UrlGenerator;
use App\Shared\Domain\Shop;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\Database\Exception\KpyNotFoundDatabaseException;
use App\ShippingCostCalculator\Domain\Builder\CarrierBuilder;
use App\ShippingCostCalculator\Domain\Service\CalculatorShippingCost;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GoogleMerchantFeedHandler
{
    private array $infoAqua;

    private array $priceShapeInfo;

    private array $idPacks;

    private array $antiparasitarios;

    private array $productosEnRoturaSinStock;

    private array $productosConPrecioEspecial;

    private array $productosConRegalos;

    private array $imagenes;

    private array $combinacionesMayoresFormatosPienso;

    private Shop $shop;

    private string $filename;

    private string $varDir;

    private int $totalCountProducts;

    /**
     * @throws KpyNotFoundDatabaseException
     */
    public function __construct(
        private readonly CalculatorShippingCost $calculatorShippingCost,
        private readonly Provider               $provider,
        private readonly CarrierBuilder         $carrierBuilder,
        private readonly CommandBus             $commandBus,
        private readonly UrlGenerator           $urlGenerator,
        #[Autowire('%kernel.project_dir%')]
        string                                  $srcDir
    )
    {
        $this->varDir = $srcDir . '/var/google/';

        $this->filename = 'kompymascotasfeed.xml';

        $this->totalCountProducts = 0;
    }

    /**
     * @throws KpyGoogleException
     */
    public function syncFeed(Shop $shop): void
    {
        $this->shop = $shop;

        $feed = $this->generateFeed($shop);

        $this->saveFeed($feed);

        $this->uploadFeed();
    }

    /**
     * @throws KpyGoogleException
     */
    private function saveFeed(string $feed): void
    {
        if (GoogleDebugMode::on()) {
            $this->filename = 'debug_' . $this->filename;
        }

        $fullPath = $this->varDir . $this->filename;

        if (file_exists($fullPath) && !is_writable($fullPath)) {
            throw new KpyGoogleException('No se puede escribir el fichero ' . $fullPath);
        }

        file_put_contents($fullPath, $feed, LOCK_EX);
    }

    private function uploadFeed(): void
    {
        if (GoogleDebugMode::off()) {
            new SFTPFileUploader()->uploadFileBySftpWithPassword(
                $_ENV['GOOGLE_SFTP_HOST'],
                $_ENV['GOOGLE_SFTP_USER'],
                $_ENV['GOOGLE_SFTP_PASSWORD'],
                $this->filename,
                $this->varDir . $this->filename,
                (int)$_ENV['GOOGLE_SFTP_PORT']
            );
        }
    }

    public function generateFeed(Shop $shop): string
    {
        $this->shop = $shop;
        $this->calculatorShippingCost->setFixedCarrierAnDestination(
            $this->carrierBuilder->getMRW(),
            Destination::PENINSULA
        );

        $this->infoAqua = $this->provider->infoAqua();

        $marcasProhibidas = $this->provider->marcasProhibidas($shop->getId());
        $productosProhibidos = $this->provider->productosProhibidos($shop->getId());
        // ya no se usa
        //$productosManuales   = $this->provider->productosManuales($shop);
        $combinacionesDesactivadas = $this->provider->combinacionesDesactivadas();
        $this->imagenes = $this->provider->cargaImagenes();
        $packs = $this->provider->getPacks();
        $this->idPacks = $this->provider->getArrayIdsPacks();
        $this->antiparasitarios = $this->provider->getProductosAntiparasitarios();
        $this->productosConPrecioEspecial = $this->provider->getProductosConPrecioEspecial($shop->getId());
        $this->productosConRegalos = $this->provider->getProductosConRegalo($this->shop->getId(), $this->shop->getLanguageId());
        $this->combinacionesMayoresFormatosPienso = $this->provider->combinacionesMayoresFormatosPienso();
        $feedAlternativeNamesProduct = $this->provider->getNamesFeed($shop->getId());
        $imagenesPersonalizadas = $this->provider->getImagenesPersonalizadas($shop->getId());
        $this->productosEnRoturaSinStock = $this->provider->getProductosEnRoturaSinStock();

        //$multipacks = $this->provider->getMultipacks($shop->getId());
        $multipacks = [];

        // todo - obtenerlos desde la entidad del bounded context que corresponda
        //$cuponesQueSoloAparecenDesdeGS = $this->provider->getCuponesQueSoloAparecenDesdeGS($shop->getId());
        $cuponesQueSoloAparecenDesdeGS = [];
        //$cuponesAplicadosDirectamenteEnGS = $this->provider->getCuponesAplicadosDirectamenteEnGS($shop->getId());
        $cuponesAplicadosDirectamenteEnGS = [];

        $measuringUnits = $this->provider->getMeasuringUnits();
        $this->priceShapeInfo = $this->provider->getPriceShapeInfo($shop->getDefaultCountry()->getISO());
        $brandsWithStockSync = $this->provider->getBrandsWithStockSync();

        // TODO - debe devolver un array de instancias de la clase Producto (Google)
        $productos = $this->provider->getProductosDesdePS($shop);

        $googleFeed = new GoogleMerchantFeed(
            $shop,
            $this->provider->ProductsWithAlternativeSku(),
        );

        $this->totalCountProducts = 0;

        if (GoogleDebugMode::off()) {
            $this->resetProductsInFeed();
        }

        foreach ($productos as $producto) {
            $sku = $producto['sku'];

            if (!array_key_exists($sku, $this->infoAqua)
                || in_array($sku, $productosProhibidos)
                || in_array($producto['attr'], $combinacionesDesactivadas)
            ) {
                continue;
            }

            // si un producto está en liquidación irá en el feed aunque la marca esté prohibida
            if (in_array($producto['id_manufacturer'], $marcasProhibidas) && $this->infoAqua[$sku]['liquidacion'] == 0) {
                continue;
            }

            // al cambiar la consulta que saca los datos de aqua para que saque todos los precios (hasta de los packs)
            // con la el sku existe en el array, los pack irian duplicados, por aquí y luego en el if que controla si tiene pack
            // los packs vienen sin nombre en el array, la informacion del pack se mete en el feed al buscar pack del producto base
            // y NO SEA UN MULTIPACK, estos sí hay que meterlos
            if ($this->infoAqua[$sku]['nombre'] == '' && !array_key_exists($sku, $multipacks)) {
                continue;
            }

            $producto['price'] = $this->infoAqua[$sku][$shop->getKeyColumnSalePrice()];

            if ($producto['price'] == 0 || $producto['pvp'] == 0) {
                continue;
            }

            if ($producto['pvp'] < $producto['price']) {
                $producto['pvp'] = $producto['price'];
            }

            $isMultipack = array_key_exists($sku, $multipacks);
            if (!$isMultipack) {
                $producto['peso'] = round($this->infoAqua[$sku]['peso'], 2);
                $producto['ean'] = str_pad(trim($this->infoAqua[$sku]['ean']), 12, "0", STR_PAD_LEFT);
                $producto['referencia'] = trim($this->infoAqua[$sku]['referencia']);
            } else {
                // el ean y la ref son las del producto principal del multipack
                $skuPrincipal = $multipacks[$sku]['principal'];
                $producto['ean'] = str_pad(trim($this->infoAqua[$skuPrincipal]['ean']), 12, "0", STR_PAD_LEFT);
                $producto['referencia'] = trim($this->infoAqua[$skuPrincipal]['referencia']);

                $pesoMultipack = 0;
                foreach ($multipacks[$sku]['items'] as $item) {
                    $pesoMultipack += round($this->infoAqua[$item]['peso'], 2);
                }
                $producto['peso'] = $pesoMultipack;
                $producto['multipack'] = count($multipacks[$sku]['items']); // para luego controlar si es multipack al añadir el nodo al xml
            }

            /*if ($this->infoAqua[$sku]['sync'] == 1 && $this->infoAqua[$sku]['stock'] < 2) {
                $producto['availabity'] = "out of stock";
            } else {
                $producto['availabity'] = "in stock";
            }*/
            // la disponibilidad va ahora en la custom_label3
            $producto['availabity'] = 'in stock';

            $producto['url'] = $this->urlGenerator->getProductLink(
                ProductCode::fromSKU($sku),
                $shop,
                $producto['category_rewrite'],
                $producto['product_rewrite']
            );
            //$producto['url'] = Context::getContext()->link->getProductLink($producto['id_product'], null, null, null, $lang, $shop, $producto['attr']);
            if (in_array($sku, $cuponesQueSoloAparecenDesdeGS)) {
                $producto['url'] .= '?' . md5('utm_gs') . '=1';
            }

            if (array_key_exists($sku, $cuponesAplicadosDirectamenteEnGS)) {
                if (strpos('?', $producto['url']) === false) {
                    $producto['url'] .= '?';
                }

                $producto['url'] .= md5('utm_gs_dto') . '=' .
                    ($cuponesAplicadosDirectamenteEnGS[$sku]['tipo'] === 'porcentaje' ? $cuponesAplicadosDirectamenteEnGS[$sku]['valor'] : '0');
            }

            $producto['gastosEnvio'] = $producto['free_shipping'] === 'yes' ? 0 : $googleFeed->getGastosEnvio($producto['price']);

            $producto['description'] = str_replace("&", "&amp;", (trim($producto['description']) == '' ?
                ucwords(strtolower(trim($this->infoAqua[$sku]['nombre']))) : trim($producto['description'])));
            $producto['description'] = str_replace("&", "&amp;", strip_tags($producto['description']));

            $categoria = $this->getCategoria($sku, $producto['mascota']);
            $producto['product_type'] = $categoria;

            if (stripos(trim($this->infoAqua[$sku]['grupoLogistico']), 'HUMEDO') !== false) {
                $nombreProducto = round((float)$this->infoAqua[$sku]['peso'] * 1000, 2) . "Gr " . $producto['name'];
                $producto['unit_pricing_measure'] = round((float)$this->infoAqua[$sku]['peso'] * 1000, 2) . " g";
                $producto['unit_pricing_base_measure'] = "100 g";
            } else if (stripos($categoria, 'alimentaci') !== false) {
                $nombreProducto = round((float)$this->infoAqua[$sku]['peso'], 2) . "Kg " . $producto['name'];
                $producto['unit_pricing_measure'] = round((float)$this->infoAqua[$sku]['peso'], 2) . " kg";
                $producto['unit_pricing_base_measure'] = "1 kg";
            } else {
                $nombreProducto = str_replace("Formato:", "", ucwords(strtolower(trim($this->infoAqua[$sku]['nombre']))));
                if (array_key_exists($sku, $measuringUnits)) {
                    $producto['unit_pricing_measure'] = $measuringUnits[$sku]['unit_pricing_measure'];
                    $producto['unit_pricing_base_measure'] = $measuringUnits[$sku]['unit_pricing_base_measure'];
                }
            }

            if (!$isMultipack) {
                $producto['name'] = $nombreProducto . $this->getEtiquetasParaNombreProducto($sku);
            }

            if (array_key_exists($sku, $feedAlternativeNamesProduct)) {
                $producto['name'] = $feedAlternativeNamesProduct[$sku];
            }

            if (array_key_exists($sku, $imagenesPersonalizadas)) {
                $producto['image'] = $imagenesPersonalizadas[$sku];
            } else {
                $producto['image'] = $this->getFirstImageUrl(
                    (int) $producto['id_product'],
                    $producto['product_rewrite'],
                    $shop);
            }

            $producto['additional_images'] = [];

            if (count($this->imagenes[$producto['id_product']]) > 1) {
                $producto['additional_images'] = array_map(function (int $imageId) use ($producto, $shop) {
                    return $this->urlGenerator->getImageLink($imageId, $shop, $producto['product_rewrite']);
                }, array_slice($this->imagenes[$producto['id_product']], 1, 10));
            }

            $producto['custom_label_0'] = $this->getCustomLabel0($sku);
            $producto['custom_label_1'] = $producto['id_manufacturer'] == 200 || $producto['OUTLET'] === 'si' || $isMultipack
                ? 'COMPETITIVO'
                : $this->getCustomLabel1($sku);
            $producto['custom_label_2'] = $this->getCustomLabel2($sku);
            $producto['custom_label_3'] = $this->getCustomLabel3($sku, in_array($producto['id_manufacturer'], $brandsWithStockSync));

            $precio = $producto['price'];
            if (array_key_exists($sku, $this->productosConPrecioEspecial)) {
                // los productos con precio especial se le calcula el margen con el descuento original no con el de special price
                $precio = $producto['pvp'] * (1 - ($this->productosConPrecioEspecial[$sku]['old_discount'] / 100));
            }

            if (array_key_exists($sku, $cuponesAplicadosDirectamenteEnGS)) {
                if ($cuponesAplicadosDirectamenteEnGS[$sku]['tipo'] === 'porcentaje') {
                    $producto['price'] = round($producto['price'] * (1 - ($cuponesAplicadosDirectamenteEnGS[$sku]['valor'] / 100)), 2);
                } else {
                    $producto['price'] = round($producto['price'] - $cuponesAplicadosDirectamenteEnGS[$sku]['valor'], 2);
                }
            }

            // si el iva de compra viene al 0 al 99% será un pack, los packs solo son de pienso
            $ivaDeCompra = $this->infoAqua[$sku]['ivaParaCompras'] > 0 ? $this->infoAqua[$sku]['ivaParaCompras'] : 1.1;
            $coste = $this->infoAqua[$sku]['costeConIva'] / $ivaDeCompra;
            $grossMargin = $this->calculateGrossMargin(
                $coste,
                $precio / (1 + ((float)$producto['iva'] / 100)),
                $producto['peso']
            );

            $producto['custom_label_4'] = $googleFeed->obtieneEtiquetaDeMargen($grossMargin, $producto['price']);

            $googleFeed->agregaProducto($producto);

            // si el producto tiene un pack se añade
            // y no está desactivado ni está prohibido
            if (array_key_exists($sku, $packs)
                && !in_array($packs[$sku]['id_pack'], $productosProhibidos)
                && !in_array(explode('-', $packs[$sku]['id_pack'])[1], $combinacionesDesactivadas)
            ) {
                $pack = $producto;

                $pack['sku'] = $packs[$sku]['id_pack'];
                $pack['quantity'] = $packs[$sku]['quantity'];
                $pack['peso'] = round($this->infoAqua[$sku]['peso'] * (int)$packs[$sku]['quantity'], 2);
                if (stripos($categoria, 'alimentaci') !== false) {
                    $nombrePack = $packs[$sku]['quantity'] . 'x' . $nombreProducto . ' ¡Pack Ahorro!';
                } else {
                    $nombrePack = str_replace("Formato:", "", ucwords(strtolower(trim($this->infoAqua[$sku]['nombre']))));
                }

                $pack['name'] = $nombrePack . $this->getEtiquetasParaNombreProducto($pack['sku']);

                if (array_key_exists($pack['sku'], $feedAlternativeNamesProduct)) {
                    $pack['name'] = $feedAlternativeNamesProduct[$pack['sku']];
                }

                if (array_key_exists($pack['sku'], $imagenesPersonalizadas)) {
                    $pack['image'] = $imagenesPersonalizadas[$pack['sku']];
                }

                $tokens = explode('-', $packs[$sku]['id_pack']);
                $pack['price'] = $this->infoAqua[$pack['sku']][$shop->getKeyColumnSalePrice()] ?? 0;
                $pack['pvp'] = round($producto['pvp'] * (int)$packs[$sku]['quantity'], 2);

                // hay packs que estan en la tabla pym_packs pero que ya no existen como producto en prestashop
                if ($pack['price'] == 0 || $pack['pvp'] == 0) {
                    continue;
                }

                if ($pack['pvp'] < $pack['price']) {
                    $pack['pvp'] = $pack['price'];
                }

                if (stripos($categoria, 'alimentaci') !== false) {
                    $nombreProducto = round((float)$this->infoAqua[$sku]['peso'], 2) . "Kg " . $producto['name'];
                    $pack['unit_pricing_measure'] = $pack['peso'] . " kg";
                }

                $pack['gastosEnvio'] = $googleFeed->getGastosEnvio($pack['price']);

                $pack['url'] = $this->urlGenerator->getProductLink(
                    ProductCode::fromSKU($pack['sku']),
                    $shop,
                    $producto['category_rewrite'],
                    $producto['product_rewrite']
                );
                //$pack['url'] = Context::getContext()->link->getProductLink($tokens[0], null, null, null, $lang, $shop, $tokens[1]);
                if (in_array($packs[$sku]['id_pack'], $cuponesQueSoloAparecenDesdeGS)) {
                    $pack['url'] .= '?' . md5('utm_gs') . '=1';
                }

                if (array_key_exists($packs[$sku]['id_pack'], $cuponesAplicadosDirectamenteEnGS)) {
                    if (strpos('?', $pack['url']) === false) {
                        $pack['url'] .= '?';
                    }
                    $pack['url'] .= md5('utm_gs_dto') . '=' .
                        ($cuponesAplicadosDirectamenteEnGS[$packs[$sku]['id_pack']]['tipo'] === 'porcentaje' ? $cuponesAplicadosDirectamenteEnGS[$packs[$sku]['id_pack']]['valor'] : 0);
                }

                $pack['custom_label_0'] = $this->getCustomLabel0($packs[$sku]['id_pack']);
                $pack['custom_label_1'] = $this->getCustomLabel1($producto['sku']);
                $pack['custom_label_2'] = $this->getCustomLabel2($packs[$sku]['id_pack']);
                $pack['custom_label_3'] = $producto['custom_label_3']; // para la disponibilidad se coge el producto que va dentro del pack

                $precio = $pack['price'];
                if (array_key_exists($pack['sku'], $this->productosConPrecioEspecial)) {
                    // los productos con precio especial se le calcula el margen con el descuento original no con el de special price
                    $precio = $pack['pvp'] * (1 - ($this->productosConPrecioEspecial[$pack['sku']]['old_discount'] / 100));
                }

                if (array_key_exists($packs[$sku]['id_pack'], $cuponesAplicadosDirectamenteEnGS)) {
                    if ($cuponesAplicadosDirectamenteEnGS[$packs[$sku]['id_pack']]['tipo'] === 'porcentaje') {
                        $pack['price'] = round($pack['price'] * (1 - ($cuponesAplicadosDirectamenteEnGS[$packs[$sku]['id_pack']]['valor'] / 100)), 2);
                    } else {
                        $pack['price'] = round($pack['price'] - $cuponesAplicadosDirectamenteEnGS[$packs[$sku]['id_pack']]['valor'], 2);
                    }
                }

                // si el iva de compra viene al 0 al 99% será un pack, los packs solo son de pienso
                $ivaDeCompra = $this->infoAqua[$sku]['ivaParaCompras'] > 0 ? $this->infoAqua[$sku]['ivaParaCompras'] : 1.1;
                $coste = $this->infoAqua[$sku]['costeConIva'] / $ivaDeCompra;
                $grossMargin = $this->calculateGrossMargin(
                    $coste,
                    $precio / (1 + ((float)$producto['iva'] / 100)),
                    $producto['peso'],
                    (int)$packs[$sku]['quantity']
                );

                $pack['custom_label_4'] = $googleFeed->obtieneEtiquetaDeMargen($grossMargin, $pack['price']);

                //print_r($pack);
                $googleFeed->agregaPack($pack);
            }
        }

        $googleFeed->closeFeed();

        if (GoogleDebugMode::off()) {
            $this->marcarEnAquaTodosLosProductosDelFeed($googleFeed->getArrayOfSkusIncluded());
        }

        $this->totalCountProducts = $googleFeed->getTotalProductos();

        return $googleFeed->getFeed();
    }

    private function resetProductsInFeed(): void
    {
        $this->commandBus->execute('kpy.command.google.reset_products_count');
    }

    private function marcarEnAquaTodosLosProductosDelFeed(array $skus): void
    {
        $this->commandBus->execute('kpy.command.google.set_products_as_included', ['skus' => $skus]);
    }


    public function getCustomLabel0(string $sku): string
    {
        return in_array($sku, $this->provider->getTopProducts()) ? 'top' : 'resto';
    }

    public function getCustomLabel1(string $sku): string
    {
        if (!array_key_exists($sku, $this->priceShapeInfo)) {
            return 'NO_COMPETITIVO';
        }

        return $this->priceShapeInfo[$sku]['caro'] === 'no' ? 'COMPETITIVO' : 'NO_COMPETITIVO';
    }

    public function getCustomLabel2(string $sku): string
    {
        [$id, $attr] = explode('-', $sku);

        // comprueba si el sku es el mayor formato (sin ser pack) del producto
        if (in_array((int)$attr, $this->combinacionesMayoresFormatosPienso, true)) {
            return 'formato_grande';
        }

        if (in_array($sku, $this->idPacks)) {
            return 'pack';
        }

        return 'resto';
    }

    public function getCustomLabel3(string $sku, bool $stock_sync): string
    {

        if (in_array($sku, $this->productosEnRoturaSinStock)) {
            return 'roturas';
        }

        if (array_key_exists($sku, $this->infoAqua) && $this->infoAqua[$sku]['stock'] < 1) {
            return 'out of stock';
        }


        return 'in stock';
    }

    public function calculateGrossMargin(float $productCostPrice, float $salesPriceWithoutVat, float $weight, int $units = 1): float
    {
        $costs = $productCostPrice
            + ($productCostPrice * 0.06) // comisión de preparación por pedido de NEFTYS
            + $this->calculatorShippingCost->calculateShippingCostByWeightWithSavedConfiguration($weight * $units);

        return round(($salesPriceWithoutVat - $costs) / $salesPriceWithoutVat * 100, 2);
    }


    public function getFirstImageUrl(int $productId, string $link_rewrite, Shop $shop): string
    {
        if (array_key_exists($productId, $this->imagenes)) {
            [$firstImage,] = $this->imagenes[$productId];
            return $this->urlGenerator->getImageLink($firstImage, $shop, $link_rewrite);
        }

        return '';
    }


    public function getCategoria($sku, $mascota): string
    {
        if (trim($this->infoAqua[$sku]['grupoLogistico']) === 'BOSKE'
            || stripos(trim($this->infoAqua[$sku]['grupoLogistico']), 'saco') !== false
            || stripos(trim($this->infoAqua[$sku]['grupoLogistico']), 'GRANDE') !== false
            || stripos(trim($this->infoAqua[$sku]['grupoLogistico']), 'MEDIANO') !== false
            || stripos(trim($this->infoAqua[$sku]['grupoLogistico']), 'PEQUE') !== false
            || stripos(trim($this->infoAqua[$sku]['grupoLogistico']), 'MINI') !== false
            || stripos(trim($this->infoAqua[$sku]['grupoLogistico']), 'HUMEDO') !== false
            || stripos(trim($this->infoAqua[$sku]['grupoLogistico']), 'SNACK') !== false
        ) {
            return sprintf('Alimentación %s', $mascota ?? 'Perro');
        }

        $id = strstr($sku, '-', true);

        if (in_array($id, $this->antiparasitarios)) {
            return 'Antiparasitarios';
        }

        if (trim($this->infoAqua[$sku]['grupoLogistico']) === 'FARMACOLOGICOS'
            || trim($this->infoAqua[$sku]['grupoLogistico']) === 'AUDEVARD'
            || trim($this->infoAqua[$sku]['grupoLogistico']) === 'CHAMPU'
        ) {
            return 'Farmacologicos';
        }

        return 'accesorios';
    }

    public function getEtiquetasParaNombreProducto($sku): string
    {
        $etiquetas = '';

        if (array_key_exists($sku, $this->productosConRegalos)) {
            $etiquetas .= ' con ' . $this->productosConRegalos[$sku];
        }

        if (array_key_exists($sku, $this->productosConPrecioEspecial) && !$this->provider->esAniversario()) {
            $etiquetas .= ' ¡-' . ceil($this->productosConPrecioEspecial[$sku]['special_discount']) . '%Dto Precio Especial!';
        }

        if ($this->provider->esBlackFriday()) {
            $etiquetas = ' Black Friday';
        }

        if ($this->provider->esAniversario()) {
            $etiquetas = ' Descuento Especial Aniversario';
        }

        return $etiquetas;
    }

    /**
     * @throws KpyCommandNotFoundException
     */
    public function totalPreviousProducts(): int
    {
        return $this->commandBus->execute('kpy.command.google.count_previous_products');
    }

    public function totalCountProducts(): int
    {
        return $this->totalCountProducts;
    }
}
