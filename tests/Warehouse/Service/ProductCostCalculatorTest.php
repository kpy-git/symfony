<?php

namespace App\Tests\Warehouse\Service;

use App\Shared\Domain\ValueObject\ProductCode;
use App\ShippingCostCalculator\Domain\Service\CalculatorShippingCost;
use App\Warehouse\Domain\ValueObject\Product;
use App\Warehouse\Domain\WarehouseFactory;
use App\Warehouse\Service\ProductCostCalculator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductCostCalculatorTest extends KernelTestCase
{

    private Product $product;

    private Product $externalProduct;

    public function testOk(): void
    {
        $this->assertTrue(true);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->product = new Product(
            ProductCode::from(0, 0),
            1,
            12,
            50
        );
        $this->externalProduct = new Product(
            ProductCode::from(0, 0),
            178,
            12,
            50
        );
    }

    public function testComputeProductCostNeftysWarehouse(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $warehouseFactory = $container->get(WarehouseFactory::class);
        $warehouse = $warehouseFactory->createFrom('NEFTYS');

        $shippingCostCalculator = $container->get(CalculatorShippingCost::class);

        $productCostCalculator = new ProductCostCalculator($shippingCostCalculator);

        $this->assertEqualsWithDelta(57.6, $productCostCalculator->computeCost($this->product, $warehouse), .000001);
    }

    public function testComputeExternalProductCostNeftysWarehouse(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $warehouseFactory = $container->get(WarehouseFactory::class);
        $warehouse = $warehouseFactory->createFrom('NEFTYS');

        $shippingCostCalculator = $container->get(CalculatorShippingCost::class);

        $productCostCalculator = new ProductCostCalculator($shippingCostCalculator);

        $this->assertEqualsWithDelta(9.1, $productCostCalculator->computeCost($this->externalProduct, $warehouse), .000001);
    }

    public function testComputeTwoExternalProductCostNeftysWarehouse(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $warehouseFactory = $container->get(WarehouseFactory::class);
        $warehouse = $warehouseFactory->createFrom('NEFTYS');

        $shippingCostCalculator = $container->get(CalculatorShippingCost::class);

        $productCostCalculator = new ProductCostCalculator($shippingCostCalculator);

        $this->assertEqualsWithDelta(11.49, $productCostCalculator->computeCost($this->externalProduct, $warehouse, 2), .000001);
    }

    public function testComputeProductCostKompyWarehouse(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $warehouseFactory = $container->get(WarehouseFactory::class);
        $warehouse = $warehouseFactory->createFrom('KOMPY');

        $shippingCostCalculator = $container->get(CalculatorShippingCost::class);

        $productCostCalculator = new ProductCostCalculator($shippingCostCalculator);

        $this->assertEqualsWithDelta(56.08, $productCostCalculator->computeCost($this->product, $warehouse), .000001);
    }

    public function testComputeExternalProductCostKompyWarehouse(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $warehouseFactory = $container->get(WarehouseFactory::class);
        $warehouse = $warehouseFactory->createFrom('KOMPY');

        $shippingCostCalculator = $container->get(CalculatorShippingCost::class);

        $productCostCalculator = new ProductCostCalculator($shippingCostCalculator);

        $this->assertEqualsWithDelta(56.08, $productCostCalculator->computeCost($this->externalProduct, $warehouse), .000001);
    }

    public function testComputeProductCostDistrivetWarehouse(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $warehouseFactory = $container->get(WarehouseFactory::class);
        $warehouse = $warehouseFactory->createFrom('DISTRIVET');

        $shippingCostCalculator = $container->get(CalculatorShippingCost::class);

        $productCostCalculator = new ProductCostCalculator($shippingCostCalculator);

        $this->assertEqualsWithDelta(56.14, $productCostCalculator->computeCost($this->product, $warehouse), .000001);
    }

    public function testComputeProductCostEvolutionPetsWarehouse(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $warehouseFactory = $container->get(WarehouseFactory::class);
        $warehouse = $warehouseFactory->createFrom('EVOLUTION_PETS');

        $shippingCostCalculator = $container->get(CalculatorShippingCost::class);

        $productCostCalculator = new ProductCostCalculator($shippingCostCalculator);

        $this->assertEqualsWithDelta(56.48, $productCostCalculator->computeCost($this->product, $warehouse), .000001);
    }
}
