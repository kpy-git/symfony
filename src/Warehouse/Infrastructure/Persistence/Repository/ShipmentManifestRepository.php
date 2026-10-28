<?php

namespace App\Warehouse\Infrastructure\Persistence\Repository;

use App\Shared\Domain\Exception\KpyException;
use App\Warehouse\Domain\ValueObject\ShipmentManifest;
use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\ShipmentEntity;
use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\Warehouse;
use App\Warehouse\Query\QueryBus;
use Doctrine\ORM\EntityManagerInterface;

readonly class ShipmentManifestRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QueryBus               $queryBus,
    )
    {
    }

    public function findAllTodayShipmentsByWarehouseName(string $warehouse): array
    {
        $warehouse = $this->entityManager
            ->getRepository(Warehouse::class)
            ->findByNameOrFail($warehouse);

        $shipments = $this->entityManager
            ->getRepository(ShipmentEntity::class)
            ->findAllTodayShipmentsByWarehouse($warehouse);

        if (empty($shipments)) {
            throw new KpyException('No se ha generado nigún envío hoy');
        }

        $shipmentsManifest = [];

        /** @var ShipmentEntity $shipment */
        foreach ($shipments as $shipment) {
            $orderDetail = $this->queryBus->fetch('kpy.warehouse.query.order_header', [
                'id_order' => $shipment->getOrderId()
            ]);

            $shipmentManifest = new ShipmentManifest()
                ->setNumber($shipment->getTrackingNumber())
                ->setReference($shipment->getOrderId())
                ->setWeight($shipment->getWeight())
                ->setPackagesCount($shipment->getParcels())
                ->setCity($orderDetail['city'])
                ->setProvince($orderDetail['state'])
                ->setRecipientName($orderDetail['name'])
                ->setCodAmount($orderDetail['crm'])
                ->setPostCode($orderDetail['postcode'])
                ->setCollectionDate($shipment->getCreatedAt());

            $shipmentsManifest[] = $shipmentManifest;
        }

        return $shipmentsManifest;
    }
}
