<?php

namespace App\Warehouse\Domain\Carrier;

use App\Warehouse\Domain\Exception\CarrierNotFoundException;
use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\Warehouse;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

readonly class CarrierFactory
{
    public function __construct(
        /** @var CarrierInterface[] $carriers */
        #[AutowireLocator('kpy.warehouse.carrier', indexAttribute: 'key')]
        private ContainerInterface $carriers,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * @throws CarrierNotFoundException
     */
    public function getByService(string $service): CarrierInterface
    {
        if (!$this->carriers->has($service)) {
            throw new CarrierNotFoundException('No hay ningún transportista disponible para el servicio solicitado, ' . $service);
        }

        return $this->carriers->get($service);
    }

    /**
     * @throws CarrierNotFoundException
     */
    public function getMRWCordoba(): CarrierInterface
    {
        return $this->getByService('mrw_cordoba');
    }

    /**
     * @throws CarrierNotFoundException
     */
    public function getByWarehouse(string $warehouseName): CarrierInterface
    {
        /** @var Warehouse $warehouse */
        $warehouse = $this->entityManager->getRepository(Warehouse::class)->findOneBy(['name' => mb_strtoupper($warehouseName)]);

        return $this->getByService($warehouse->getCarrierService());
    }
}
