<?php

namespace App\Warehouse\Domain\Carrier;

use App\Warehouse\Domain\Exception\CarrierNotFoundException;
use App\Warehouse\Domain\ExpeditionableInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class CarrierFactory
{
    public function __construct(
        /** @var ExpeditionableInterface[] $carriers */
        #[AutowireIterator('kpy.warehouse.carrier')]
        private iterable $carriers,
    )
    {
    }

    /**
     * @throws CarrierNotFoundException
     */
    public function getByService(string $service): ExpeditionableInterface
    {
        foreach ($this->carriers as $carrier) {
            if ($carrier->associatedService() === $service) {
                return $carrier;
            }
        }

        throw new CarrierNotFoundException('No hay ningún transportista disponible para el servicio solicitado');
    }

    /**
     * @throws CarrierNotFoundException
     */
    public function getMRWCordoba(): ExpeditionableInterface
    {
        return $this->getByService('CORDOBA');
    }
}
