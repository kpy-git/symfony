<?php

namespace App\Google\Domain;

use App\Shared\Domain\Destination;
use App\Shared\Domain\Shop;

class GoogleMerchantFeed
{
    public string $dominio;

    public string $nombre;

    public int $productosEnFeed;

    public int $numeroProductosAnteriores; // el numero de productos que hay en el feed de la ultima ejecución

    public array $idsProductosEnFeed;

    public string $salida;

    public float $gastosEnvio;

    public float $limiteParaGastosEnvio;

    public string $pais;

    private string $outputFilename;

    public function __construct(
        private readonly Shop $shop,
        protected array       $skusWithCodeAlternative
    )
    {
        $this->outputFilename = 'feedkompymascotas.xml';

        if (GoogleDebugMode::on()) {
            $this->outputFilename = 'debug_' . $this->outputFilename;
        }

        $this->dominio = $shop->getDomain();
        $this->gastosEnvio = $shop->shippingPriceByDestination(Destination::PENINSULA);
        $this->limiteParaGastosEnvio = $shop->priceLimitToShippingFree();
        $this->pais = $shop->getDefaultCountry()->getISO();

        $this->nombre = str_replace('https://', '', $this->dominio);
        $this->productosEnFeed = 0;
        $this->idsProductosEnFeed = [];
        $this->salida = '';

        $this->inicializaCabecera();
    }

    private function inicializaCabecera(): void
    {
        $this->salida = "<?xml version='1.0' encoding='UTF-8' ?>\n";
        $this->salida .= "<feed xmlns='http://www.w3.org/2005/Atom' xmlns:g='http://base.google.com/ns/1.0' encoding='UTF-8' >\n";
        $this->salida .= "<title>" . $this->nombre . "</title>\n";
        $this->salida .= "<link href='" . $this->dominio . "' rel='alternate' type='text/html'/>\n";
        $this->salida .= "<modified>" . date("Y-m-d H:i:s") . "</modified>\n";
        $this->salida .= "<author><name>" . $this->nombre . "</name></author>\n";
    }

    public function closeFeed(): void
    {
        $this->salida .= "</feed>\n";
    }

    public function obtieneNumeroProductosAnteriores(): void
    {
        $this->numeroProductosAnteriores = (int)DbPymMssql::getInstance()->valor(
            "SELECT COUNT(*) FROM DATPYMPRDPRICES01 WITH(NOLOCK) WHERE GSHOPINGESP=1");
    }

    public function obtieneEtiquetaDeMargen(float $margin, float $salesPrice): string
    {
        if ($salesPrice < $this->shop->getLimitToCalculateShippingCost()) {
            if ($margin >= 36) {
                return 'MARGEN_ALTO';
            }

            if ($margin >= 32) {
                return 'MARGEN_SEMI_ALTO';
            }

            return 'MARGEN_NORMAL';
        }

        if ($margin >= 26) {
            return 'MARGEN_ALTO';
        }

        if ($margin >= 22) {
            return 'MARGEN_SEMI_ALTO';
        }

        return 'MARGEN_NORMAL';
    }

    public function agregaProducto(array $producto): void
    {
        if ($this->productoNoEstaEnFeed($producto['sku'])) {
            $nodo = "<entry>\n";
            $nodo .= $this->generaNodo($producto);
            $nodo .= "</entry>\n";

            $this->salida .= $nodo;
            $this->marcaProductoComoIncluidoEnFeed($producto['sku']);
        }
    }

    protected function productoNoEstaEnFeed(string $sku): bool
    {
        return in_array(trim($sku), $this->idsProductosEnFeed) === false;
    }

    protected function generaNodo(array $producto): string
    {
        $nodo = "<g:id>" . $producto['sku'] . "</g:id>\n";
        if (array_key_exists($producto['sku'], $this->skusWithCodeAlternative)) {
            [$id, $attr] = explode('-', $producto['sku']);
            $nodo = "<g:id>{$id}-{$this->skusWithCodeAlternative[$producto['sku']]}-{$attr}</g:id>\n";
        }

        $nodo .= "<title>" . str_replace("&", "&amp;", $producto['name']) . "</title>\n";
        $nodo .= "<link>" . $producto['url'] . "</link>\n";
        $nodo .= "<g:price>" . $producto['pvp'] . "</g:price>\n";
        if (array_key_exists('unit_pricing_measure', $producto)) {
            $nodo .= "<g:unit_pricing_measure>" . $producto['unit_pricing_measure'] . "</g:unit_pricing_measure>\n";
        }
        if (array_key_exists('unit_pricing_base_measure', $producto)) {
            $nodo .= "<g:unit_pricing_base_measure>" . $producto['unit_pricing_base_measure'] . "</g:unit_pricing_base_measure>\n";
        }
        $nodo .= "<g:sale_price>" . $producto['price'] . "</g:sale_price>\n";
        $nodo .= "<g:description>" . str_replace('&nbsp;', ' ', $producto['description']) . "</g:description>\n";
        $nodo .= "<g:condition>new</g:condition>\n";
        $nodo .= "<g:mpn>" . $producto['referencia'] . "</g:mpn>\n";
        $nodo .= "<g:image_link>" . $producto['image'] . "</g:image_link>\n";

        if (!empty($producto['additional_images'])) {
            foreach ($producto['additional_images'] as $image) {
                $nodo .= "<g:additional_image_link>" . $image . "</g:additional_image_link>\n";
            }
        }

        $nodo .= "<g:availability>" . $producto['availabity'] . "</g:availability>\n";
        $nodo .= "<g:brand>" . str_replace("&", "&amp;", $producto['fabricante']) . "</g:brand>\n";
        $nodo .= "<g:google_product_category>Animales &gt; Productos para mascotas</g:google_product_category>\n";
        $nodo .= "<g:product_type>" . $producto['product_type'] . "</g:product_type>\n";
        $nodo .= "<g:custom_label_0>" . str_replace("&", "&amp;", $producto['custom_label_0']) . "</g:custom_label_0>\n";
        if (array_key_exists('custom_label_1', $producto)) {
            $nodo .= "<g:custom_label_1>" . str_replace("&", "&amp;", $producto['custom_label_1']) . "</g:custom_label_1>\n";
        }
        if (array_key_exists('custom_label_2', $producto)) {
            $nodo .= "<g:custom_label_2>" . str_replace("&", "&amp;", $producto['custom_label_2']) . "</g:custom_label_2>\n";
        }
        if (array_key_exists('custom_label_3', $producto)) {
            $nodo .= "<g:custom_label_3>" . str_replace("&", "&amp;", $producto['custom_label_3']) . "</g:custom_label_3>\n";
        }
        if (array_key_exists('custom_label_4', $producto)) {
            $nodo .= "<g:custom_label_4>" . str_replace("&", "&amp;", $producto['custom_label_4']) . "</g:custom_label_4>\n";
        }
        /*$nodo .= "<g:shipping>\n";
        $nodo .= "<g:country>" . $this->pais . "</g:country>\n";
        $nodo .= "<g:service>Standard</g:service>\n";
        $nodo .= "<g:price>" . $producto['gastosEnvio'] . "</g:price>\n";
        $nodo .= "<g:min_handling_time>0</g:min_handling_time>\n";
        $nodo .= "<g:max_handling_time>1</g:max_handling_time>\n";
        $nodo .= "<g:min_transit_time>0</g:min_transit_time>\n";
        $nodo .= "<g:max_transit_time>1</g:max_transit_time>\n";
        $nodo .= "</g:shipping>\n";
        $nodo .= "<g:free_shipping_threshold>\n";
        $nodo .= "<g:country>" . $this->pais . "</g:country>\n";
        $nodo .= "<g:price_threshold>" . $this->limiteParaGastosEnvio . "</g:price_threshold>\n";
        $nodo .= "</g:free_shipping_threshold>\n";*/
        $nodo .= "<g:shipping_weight>" . $producto['peso'] . " kg</g:shipping_weight>\n";
        $nodo .= "<g:gtin>" . $producto['ean'] . "</g:gtin>\n";

        if (array_key_exists('multipack', $producto)) {
            $nodo .= "<g:multipack>{$producto['multipack']}</g:multipack>\n";
        }

        return $nodo;
    }


    private function marcaProductoComoIncluidoEnFeed(string $sku): void
    {
        $this->productosEnFeed++;
        $this->idsProductosEnFeed[] = trim($sku);
    }

    public function agregaPack(array $pack): void
    {
        if ($this->productoNoEstaEnFeed($pack['sku'])) {
            $nodo = "<entry>\n";
            $nodo .= $this->generaNodo($pack);
            $nodo .= "<g:multipack>{$pack['quantity']}</g:multipack>\n";
            $nodo .= "</entry>\n";

            $this->salida .= $nodo;
            $this->marcaProductoComoIncluidoEnFeed($pack['sku']);
        }
    }

    public function getDominio(): string
    {
        return $this->dominio;
    }

    public function getGastosEnvio($precio): float
    {
        if ((float)$precio <= $this->limiteParaGastosEnvio) {
            return $this->gastosEnvio;
        }

        return 0;
    }

    public function getTotalProductos(): int
    {
        return $this->productosEnFeed;
    }

    public function getNombreFicheroFeed(): string
    {
        return $this->outputFilename;
    }

    public function getNumeroProductosAnteriores(): int
    {
        return $this->numeroProductosAnteriores;
    }

    public function getFeed(): string
    {
        return $this->salida;
    }
}
