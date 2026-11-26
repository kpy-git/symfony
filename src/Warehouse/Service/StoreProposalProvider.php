<?php

namespace App\Warehouse\Service;

use App\Shared\Domain\Service\UrlGenerator;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Domain\ValueObject\Tax;
use App\Warehouse\Domain\ValueObject\ProposalProduct;
use App\Warehouse\Domain\ValueObject\SupplierPreview;
use App\Warehouse\Query\QueryBus;

readonly class StoreProposalProvider
{
    public function __construct(
        private QueryBus $queryBus,
        private UrlGenerator $urlGenerator,
    )
    {
    }

    public function getSuppliersPreview(): array
    {
        return array_map(static function(array $row): SupplierPreview {
            return new SupplierPreview(trim($row['NIF']), trim($row['NOMBRE']))
                ->setMinAmount((float)$row['PED_MIN_EURO'])
                ->setTotal((float)$row['TOTAL'])
                ->setLines((int)$row['LINEAS'])
                ->setCutoffTime($row['HORA_CORTE']);
        }, $this->queryBus->fetch('kpy.warehouse.query.proposal_suppliers_preview'));
    }

    public function getSupplierProducts(string $supplierCode): array
    {
        return array_map(function(array $row): ProposalProduct {
            $productCode = ProductCode::fromSKU($row['CODIGO']);

            return new ProposalProduct()
                ->setProductCode($productCode)
                ->setUnits((int)$row['REQUERIDAS'])
                ->setUnitPriceTaxExcl((float)$row['PRICE'])
                ->setName(trim($row['PRODUCTO']))
                ->setMinimalQuantity((int)$row['UDS_MIN_COMPRA'])
                ->setStockAvailable((int)$row['STOCK_DISPONIBLE'])
                ->setTax(Tax::from((int)$row['TIPOIVA']))
                ->setReference($row['REF'])
                ->setEan($row['EAN'])
                ->setUrl($this->urlGenerator->generateProductLink($productCode))
                ->setWeight((float)$row['PESO'])
                ->setVolume((float)$row['VOLUMEN'])
                ->setTariff((float)$row['TARIFA'])
                ->setDiscount((float)$row['DESCUENTO']);
        }, $this->queryBus->fetch('kpy.warehouse.query.proposal_products_supplier', [
            'supplier' => $supplierCode,
        ]));
    }
}
