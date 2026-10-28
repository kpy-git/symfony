<?php

namespace App\Warehouse\Infrastructure\Persistence\Doctrine\Repository;

use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\ShipmentEntity;
use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\Warehouse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShipmentEntity>
 */
class ShipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShipmentEntity::class);
    }

    public function findAllTodayShipmentsByWarehouse(Warehouse $warehouse): array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager
            ->createQuery(
                'SELECT s
                 FROM App\Warehouse\Infrastructure\Persistence\Doctrine\Model\ShipmentEntity s
                 WHERE s.warehouse = :warehouse AND s.createdAt >= :today'
            )->setParameter('warehouse', $warehouse->getId())
            ->setParameter('today', date('Y-m-d 00:00:00'));

        return $query->getResult();
    }

    //    /**
    //     * @return Shipment[] Returns an array of Shipment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Shipment
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
