<?php

namespace App\Warehouse\Command;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Shared\Infrastructure\Database\Exception\KpySqlException;

readonly class PrebuyRemoveQuantityProductCommand implements CommandInterface
{

    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.command.prebuy_product_remove_quantity';
    }

    public function execute(array $params = []): bool
    {
        try {
            $this->aquaDatabase->beginTransaction();

            $currentQuantity = (int)$this->aquaDatabase->getValue(
                "select isnull((SELECT UNIDADES FROM DATKPYPREBUY03 WITH(NOLOCK) WHERE CODIGO = '{$params['sku']}'), 0) as unidades"
            );

            if ($currentQuantity <= $params['quantity']) {
                $this->aquaDatabase->execute("DELETE FROM DATKPYPREBUY03 WHERE CODIGO = '{$params['sku']}'");

            } else {

                $stmt = $this->aquaDatabase->prepare("UPDATE DATKPYPREBUY03 SET UNIDADES=? WHERE CODIGO=?");

                $stmt->bindValue(1, $currentQuantity - $params['quantity']);
                $stmt->bindValue(2, $params['sku']);

                $stmt->execute();
            }

            $this->aquaDatabase->commit();

            return true;

        } catch (\PDOException $e) {
            $this->aquaDatabase->rollBack();

            throw new KpySqlException(
                $e->getMessage(),
                __METHOD__,
                $this->aquaDatabase->getLastSql(),
                $this->aquaDatabase->getSqlError()
            );
        }
    }
}
