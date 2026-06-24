<?php

namespace App\ShippingCostCalculator\Infrastructure\Persistence\Repository;

use App\Shared\Domain\Aggregate\Destination;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\ShippingCostCalculator\Domain\Aggregate\Range;
use App\ShippingCostCalculator\Domain\Aggregate\RangeAdditionalPerKg;
use App\ShippingCostCalculator\Domain\Aggregate\RangeInterface;
use App\ShippingCostCalculator\Domain\Exception\ShippingCostException;
use App\ShippingCostCalculator\Domain\Repository\RangeRepositoryInterface;

readonly class RangeRepository implements RangeRepositoryInterface
{
    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    /**
     * @return RangeInterface[]
     * @throws ShippingCostException
     */
    public function findAllByServiceAndDestination(string $service, Destination $destination): array
    {
        $results = $this->aquaDatabase->execute(
            "SELECT C.CODE, T.DESDE, T.HASTA, T.PRECIO, T.ADICIONAL, T.PRECIOADICIONAL, T.DESDEADICIONAL
                FROM DATCHAOTICCARRIER03 C WITH(NOLOCK)
                INNER JOIN DATWMRTARIFAPRECIO T WITH(NOLOCK)
                    ON T.SERVICIO = C.CODE AND T.DESTINO = '{$destination->value}'
                WHERE C.CODE = '{$service}'
                ORDER BY HASTA"
        );

        if (empty($results)) {
            throw new ShippingCostException('Range not found');
        }

        return array_map(static function (array $result): RangeInterface {
            if ($result['ADICIONAL']) {
                return new RangeAdditionalPerKg(
                    (float)$result['DESDEADICIONAL'],
                    (float)$result['HASTA']
                );
            }

            return new Range(
                (float)$result['DESDE'],
                (float)$result['HASTA'],
                (float)$result['PRECIO'],
            );

        }, $results);
    }
}
