<?php

namespace App\Warehouse\Infrastructure\Persistence\Repository;

use App\Shared\Domain\Exception\KpyEntityNotFoundException;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Warehouse\Domain\Supplier;

readonly class SupplierRepository
{
    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    /**
     * @throws KpyEntityNotFoundException
     */
    public function findByNifOrFail(string $nif): Supplier
    {
        $sql = "SELECT Isnull(FP.CUENTA,'') AS CUENTA,
                    IsNull(FP.TIPOEFECTO,'') AS EFECTO,
                    PROV.DESCUENTO1,
                    PROV.DESCUENTO2,
                    PROV.DESCUENTO3,
                    PROV.DESCUENTO4,
                    PROV.DESCUENTO5,
                    PROV.DESCUENTO6,
                    PROV.ENTFPAGO,
                    PROV.EXENTO_IVA,
                    RTRIM(PROV.COMERCIAL) AS NOMBRE,
                    PROV.PED_MIN_EURO,
                    PROV.HORA_CORTE,
                    PROV.DIAS_SERVICIO
                FROM DATPROV03 PROV WITH(NOLOCK)
				LEFT JOIN DATFP03 FP WITH (NOLOCK) ON FP.NUMERO = PROV.ENTFPAGO
				WHERE PROV.NIF='{$nif}'";

        $results = $this->aquaDatabase->getRow($sql);

        if (empty($results)) {
            throw new KpyEntityNotFoundException(sprintf('No existe ningún proveedor con el código "%s"', $nif));
        }

        $supplier = new Supplier()
            ->setCode($nif)
            ->setExentoIVA(strtoupper(trim($results['EXENTO_IVA'])) === 'S')
            ->setNCuenta($results['CUENTA'])
            ->setNEfecto($results['EFECTO'])
            ->setFormaPago($results['ENTFPAGO'])
            ->setCutoffTime($results['HORA_CORTE'])
            ->setName($results['NOMBRE'])
            ->setMinPurchaseAmount((float)$results['PED_MIN_EURO'])
            ->setFulfillmentDays((int)$results['DIAS_SERVICIO']);


        $discounts = [];
        for ($i = 1; $i < 7; $i++) {
            $discounts[] = (float)$results['DESCUENTO' . $i];
        }

        $supplier->setDescuentos($discounts);

        return $supplier;
    }
}
