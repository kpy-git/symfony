<?php

namespace App\Warehouse\Domain\CostStrategy;

use App\Warehouse\Domain\Exception\WarehouseException;
use App\Warehouse\Domain\ValueObject\ExternalProductFulfillmentCost;
use App\Warehouse\Domain\ValueObject\Product;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

class NeftysWarehouseCostStrategy implements WarehouseCostStrategyInterface
{
    private float $commission;

    private ExternalProductFulfillmentCost $externalProductsFulfillmentCost;

    /**
     * @throws WarehouseException
     */
    public function __construct(
        Filesystem $filesystem,
        #[Autowire('%kpy.warehouse.neftys_commission%')]
        float      $commissionPercentage,
        #[Autowire('%kpy.warehouse.var_dir%')]
        string     $varDir,
    )
    {
        $this->commission = 1 + ($commissionPercentage / 100);

        $filePath = $varDir . '/NeftysExternalProductsCost.json';

        if (!$filesystem->exists($filePath)) {
            throw new WarehouseException('El almacén Neftys necesita un archivo con la configuración para el coste de los productos externos');
        }

        $data = json_decode($filesystem->readFile($filePath), true, 512, JSON_THROW_ON_ERROR);

        $this->externalProductsFulfillmentCost = new ExternalProductFulfillmentCost(
            (float)$data["singleItemUpTo5Kg"],
            (float)$data["additionalItemsUpTo5Kg"],
            (float)$data["singleItemStartingAt5Kg"],
            (float)$data["additionalItemsStartingAt5Kg"],
        );
    }

    public function computeFinalCostPrice(Product $product, int $quantity = 1): float
    {
        if ($this->isExternal($product)) {
            return $this->computeExternalProductFulfillmentCost($quantity, $product->getWeight());
        }

        return $product->getCostPrice() * $this->commission * $quantity;
    }

    public function isExternal(Product $product): bool
    {
        return $product->isBoske();
    }

    public function getType(): CostStrategyType
    {
        return CostStrategyType::NEFTYS;
    }

    public function computeExternalProductFulfillmentCost(int $quantity, float $weight): float
    {
        $singleItemCost = match (true) {
            $weight < 5 => $this->externalProductsFulfillmentCost->getSingleItemUpTo5Kg(),
            default => $this->externalProductsFulfillmentCost->getSingleItemStartingAt5Kg()
        };

        $additionalUnitsCost = match (true) {
            $weight < 5 => $this->externalProductsFulfillmentCost->getAdditionalItemsUpTo5Kg(),
            default => $this->externalProductsFulfillmentCost->getAdditionalItemsStartingAt5Kg()
        };

        return round($singleItemCost + (($quantity - 1) * $additionalUnitsCost), 6);
    }
}
