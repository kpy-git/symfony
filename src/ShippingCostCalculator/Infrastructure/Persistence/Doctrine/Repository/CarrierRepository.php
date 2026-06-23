<?php

namespace App\ShippingCostCalculator\Infrastructure\Persistence\Doctrine\Repository;

use App\ShippingCostCalculator\Domain\Exception\CarrierNotFoundException;
use App\ShippingCostCalculator\Domain\Repository\CarrierRepositoryInterface;
use App\ShippingCostCalculator\Infrastructure\Persistence\Doctrine\Entity\Carrier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Carrier>
 */
class CarrierRepository extends ServiceEntityRepository implements CarrierRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carrier::class);
    }

    //    /**
    //     * @return Carrier[] Returns an array of Carrier objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Carrier
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * @throws CarrierNotFoundException
     */
    public function findById(int $id): \App\ShippingCostCalculator\Domain\Carrier
    {
        $carrier = $this->find($id);

        if (!$carrier) {
            throw new CarrierNotFoundException('Carrier not found');
        }

        return $this->createByCarrierDoctrine($carrier);
    }

    /**
     * @throws CarrierNotFoundException
     */
    public function findByName(string $name): \App\ShippingCostCalculator\Domain\Carrier
    {
        $carrier = $this->findOneBy(['name' => $name]);

        if (!$carrier) {
            throw new CarrierNotFoundException('Carrier not found');
        }

        return $this->createByCarrierDoctrine($carrier);
    }

    /**
     * @throws CarrierNotFoundException
     */
    public function findByService(string $service): \App\ShippingCostCalculator\Domain\Carrier
    {
        $carrier = $this->findOneBy(['idServiceAqua' => $service]);

        if (!$carrier) {
            throw new CarrierNotFoundException('Carrier not found');
        }

        return $this->createByCarrierDoctrine($carrier);
    }

    private function createByCarrierDoctrine(Carrier $carrier): \App\ShippingCostCalculator\Domain\Carrier
    {
        return new \App\ShippingCostCalculator\Domain\Carrier(
            $carrier->getId(),
            $carrier->getName(),
            $carrier->getIdServiceAqua(),
            $carrier->getMaxShippingWeight(),
            $carrier->isMultiparcelAllowed(),
            $carrier->getMaxParcelWeight()
        );
    }

    public function add(\App\ShippingCostCalculator\Domain\Carrier $carrierBoundedContext): void
    {
        $carrierDoctrine = new Carrier();

        if ($carrierBoundedContext->getId()) {
            $carrierDoctrine->setId($carrierBoundedContext->getId());
        }

        $carrierDoctrine
            ->setName((string)$carrierBoundedContext)
            ->setIdServiceAqua($carrierBoundedContext->getServiceId())
            ->setMaxShippingWeight($carrierBoundedContext->getMaxShippingWeight())
            ->setMultiparcelAllowed($carrierBoundedContext->isMultiparcelAllowed())
            ->setMaxParcelWeight($carrierBoundedContext->getMaxParcelWeight());

        $this->getEntityManager()->persist($carrierDoctrine);
        $this->getEntityManager()->flush();
    }


}
