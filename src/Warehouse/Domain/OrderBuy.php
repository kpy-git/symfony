<?php

namespace App\Warehouse\Domain;

use App\Shared\Domain\ValueObject\Tax;
use App\Warehouse\Domain\ValueObject\ProposalProduct;

class OrderBuy
{
    private string $numeroDocumento;

    private int $lineasPendientes;

    private int $unidades;

    private float $peso;

    private float $volumen;

    /** @var ProposalProduct[] */
    private array $productos;

    private Supplier $proveedor;

    private float $total;

    private float $totalIVA;

    private float $totalTarifa;

    private float $baseIVA1;

    private float $baseIVA2;

    private float $baseIVA3;

    private string $fecha;

    private string $hora;

    private string $tipoOperacion;

    private string $almacen;

    private string $areaDescarga;

    private string $muelle;

    private string $moneda;

    private string $formato;

    private string $usuario;

    private int $precisionOperacion;

    private int $precisionLinea;

    private bool $pedidoPorCamion;

    public function __construct()
    {
        $this->productos = [];
        $this->numeroDocumento = '';

        $this->total = 0;
        $this->totalIVA = 0;
        $this->totalTarifa = 0;
        $this->baseIVA1 = 0;
        $this->baseIVA2 = 0;
        $this->baseIVA3 = 0;

        $this->lineasPendientes = 0;
        $this->unidades = 0;
        $this->peso = 0.0;
        $this->volumen = 0.0;

        $this->fecha = date('d/m/Y');
        $this->hora = date('H:i:s');

        $this->tipoOperacion = 'P';
        $this->almacen = $_ENV['AQUA_DEFAULT_WAREHOUSE'];
        $this->areaDescarga = 'DESC';
        $this->muelle = 'MUEN';
        $this->moneda = 'EUR';
        $this->formato = 'BUY_BUYS';
        $this->precisionOperacion = 2;
        $this->precisionLinea = 2;

        $this->usuario = 'BOT';
        $this->pedidoPorCamion = false;
    }

    public function setProveedor(Supplier $proveedor): self
    {
        $this->proveedor = $proveedor;
        return $this;
    }

    public function setPedidoPorCamion(bool $camion): self
    {
        $this->pedidoPorCamion = $camion;
        return $this;
    }

    public function getSupplier(): Supplier
    {
        return $this->proveedor;
    }

    public function agregaProducto(ProposalProduct $producto): void
    {
        $this->productos[] = $producto;

        $this->totalTarifa = (float)bcadd($this->totalTarifa, bcmul($producto->getTariff(), $producto->getUnits(), 6), $this->precisionLinea);

        $productFinalPrice = (float)bcmul($producto->getTotalPriceTaxExcl(), $this->proveedor->getDescuentoEnCascadaAcumulado(), $this->precisionLinea);
        $this->total += $productFinalPrice;

        if (!$this->proveedor->isExentoIVA()) {
            $this->totalIVA += ($producto->getTax()->getTaxRate() * $productFinalPrice) - $productFinalPrice;
            $this->acumulaBase($producto->getTotalPriceTaxExcl(), $producto->getTax());
        }

        $this->lineasPendientes++;
        $this->unidades += $producto->getUnits();

        $this->peso += $producto->getUnits() * $producto->getWeight();
        $this->volumen += $producto->getVolume() * $producto->getUnits();
    }

    private function acumulaBase(float $base, Tax $tax): void
    {
        if ($tax === Tax::_10) {
            $this->baseIVA3 += $base;
        } else if ($tax === Tax::_21) {
            $this->baseIVA2 += $base;
        } else if ($tax === Tax::_4) {
            $this->baseIVA1 += $base;
        }
    }

    public function getProductos(): array
    {
        return $this->productos;
    }

    public function getNumeroDocumento(): string
    {
        return $this->numeroDocumento;
    }

    public function isPedidoCamion(): bool
    {
        return $this->pedidoPorCamion;
    }

    public function getLineasPendientes(): int
    {
        return $this->lineasPendientes;
    }

    public function getUnidades(): int
    {
        return $this->unidades;
    }

    public function getPeso(): float
    {
        return $this->peso;
    }

    public function getVolumen(): float
    {
        return $this->volumen;
    }

    public function getMuelle(): string
    {
        return $this->muelle;
    }

    public function getMoneda(): string
    {
        return $this->moneda;
    }

    public function getFormato(): string
    {
        return $this->formato;
    }

    public function getUsuario(): string
    {
        return $this->usuario;
    }

    public function getPrecisionOperacion(): int
    {
        return $this->precisionOperacion;
    }

    public function getPrecisionLinea(): int
    {
        return $this->precisionLinea;
    }

    public function getAreaDescarga(): string
    {
        return $this->areaDescarga;
    }

    public function getAlmacen(): string
    {
        return $this->almacen;
    }

    public function getHora(): string
    {
        return $this->hora;
    }

    public function getTipoOperacion(): string
    {
        return $this->tipoOperacion;
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getTotalIVA(): float
    {
        return $this->totalIVA;
    }

    public function getTotalTarifa(): float
    {
        return $this->totalTarifa;
    }

    public function getBaseIVA1(): float
    {
        return $this->baseIVA1;
    }

    public function getBaseIVA2(): float
    {
        return $this->baseIVA2;
    }

    public function getBaseIVA3(): float
    {
        return $this->baseIVA3;
    }

    public function setDocNumber(string $docNumber): void
    {
        $this->numeroDocumento = $docNumber;
    }
}
