<?php

namespace App\Warehouse\Infrastructure\Persistence\Repository;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Service\AquaCountersHandler;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Shared\Infrastructure\Database\Exception\KpySqlException;
use App\Warehouse\Domain\OrderBuy;
use App\Warehouse\Domain\ValueObject\ProposalProduct;

readonly class AquaOrderBuyRepository
{
    public function __construct(
        private DatabaseInterface   $aquaDatabase,
        private AquaCountersHandler $aquaCountersHandler,
    )
    {
    }

    /**
     * @throws KpySqlException
     * @throws KpyException
     */
    public function save(OrderBuy $order): void
    {
        if (empty($order->getProductos())) {
            throw new KpyException('No se puede guardar un pedido de compra sin productos');
        }

        $exento = $order->getSupplier()->isExentoIVA() ? 1 : 0;
        $pedidoPorCamion = $order->isPedidoCamion() ? 'CAMION' : '';
        $descuentosEnCascada = $order->getSupplier()->getDescuentos();

        $operation = $this->aquaCountersHandler->getNewOperationNumber();
        $positions = $this->aquaCountersHandler->getNewRangeOperationNumber(count($order->getProductos()));

        if (count($positions) !== count($order->getProductos())) {
            throw new KpyException('No se han obtenido el número de posiciones adecuadas');
        }

        $docNumber = $this->aquaCountersHandler->getNewOrderBuyDocNumber();

        try {
            $this->aquaDatabase->beginTransaction();

            $sqlOperacion = "
                INSERT INTO DATOP03 (
                    NUMERO, PENDIENTES, FECHA, NUMERO_DOC, FORMAP, TIPOOPER, TOTAL, TOTIVA, ARTICULOS,
                    NEFECTO, NIF, NCUENTA, BASEIVA1, BASEIVA2, BASEIVA3, PCIVA1, PCIVA2, PCIVA3,
                    PCREC1, PCREC2, PCREC3, T_TARIFA, HORA, CENTRO, UNIDADES, PESO, VOLUMEN, MONEDA,
                    REVISADO, FORMATO, NUMPRECISI, USUARIO, NUMPREC_LI, TOTALDEC, AREAUNLOAD, WHARF,
                    TOTALIRPF, TOTIVAIRPF, TOTIVAIRPF1, TOTIVAIRPF2, TOTIVAIRPF3, TOTALDECIRPF,
                    EXENTOIVA, FCOSTE, EUROTODIVI, EUROTOMONE, F_PREVISTA, COMENT_1,
                    DES1, DES2, DES3, DES4, DES5, DES6
                ) VALUES (
                    :numero, :pendientes, :fecha, :numero_doc, :formap, :tipooper, :total, :totiva, :articulos,
                    :nefecto, :nif, :ncuenta, :baseiva1, :baseiva2, :baseiva3, :pciva1, :pciva2, :pciva3,
                    :pcrec1, :pcrec2, :pcrec3, :t_tarifa, :hora, :centro, :unidades, :peso, :volumen, :moneda,
                    :revisado, :formato, :numprecisi, :usuario, :numprec_li, :totaldec, :areaunload, :wharf,
                    :totalirpf, :totivairpf, :totivairpf1, :totivairpf2, :totivairpf3, :totaldecirpf,
                    :exentoiva, :fcoste, :eurotodivi, :eurotomone, :f_prevista, :coment_1,
                    :des1, :des2, :des3, :des4, :des5, :des6
                )
            ";

            $stmt = $this->aquaDatabase->prepare($sqlOperacion);

            $stmt->bindValue('numero', $operation, \PDO::PARAM_INT);
            $stmt->bindValue('pendientes', $order->getLineasPendientes());
            $stmt->bindValue('fecha', $order->getFecha());
            $stmt->bindValue('numero_doc', $docNumber);
            $stmt->bindValue('formap', $order->getSupplier()->getFormaPago());
            $stmt->bindValue('tipooper', $order->getTipoOperacion());
            $stmt->bindValue('total', round($order->getTotal(), $order->getPrecisionOperacion()));
            $stmt->bindValue('totiva', round($order->getTotalIVA(), $order->getPrecisionOperacion()));
            $stmt->bindValue('articulos', $order->getTotal());
            $stmt->bindValue('nefecto', $order->getSupplier()->getNEfecto());
            $stmt->bindValue('nif', $order->getSupplier()->getCode());
            $stmt->bindValue('ncuenta', $order->getSupplier()->getNCuenta());
            $stmt->bindValue('baseiva1', round($order->getBaseIVA1(), $order->getPrecisionOperacion()));
            $stmt->bindValue('baseiva2', round($order->getBaseIVA2(), $order->getPrecisionOperacion()));
            $stmt->bindValue('baseiva3', round($order->getBaseIVA3(), $order->getPrecisionOperacion()));
            $stmt->bindValue('pciva1', 4);
            $stmt->bindValue('pciva2', 21);
            $stmt->bindValue('pciva3', 10);
            $stmt->bindValue('pcrec1', 0.5);
            $stmt->bindValue('pcrec2', 5.2);
            $stmt->bindValue('pcrec3', 1.4);
            $stmt->bindValue('t_tarifa', round($order->getTotalTarifa(), $order->getPrecisionOperacion()));
            $stmt->bindValue('hora', $order->getHora());
            $stmt->bindValue('centro', $order->getAlmacen());
            $stmt->bindValue('unidades', $order->getUnidades());
            $stmt->bindValue('peso', $order->getPeso());
            $stmt->bindValue('volumen', $order->getVolumen());
            $stmt->bindValue('moneda', $order->getMoneda());
            $stmt->bindValue('revisado', $order->getUsuario());
            $stmt->bindValue('formato', $order->getFormato());
            $stmt->bindValue('numprecisi', $order->getPrecisionOperacion());
            $stmt->bindValue('usuario', $order->getUsuario());
            $stmt->bindValue('numprec_li', $order->getPrecisionLinea());
            $stmt->bindValue('totaldec', round($order->getTotal(), $order->getPrecisionOperacion()));
            $stmt->bindValue('areaunload', $order->getAreaDescarga());
            $stmt->bindValue('wharf', $order->getMuelle());
            $stmt->bindValue('totalirpf', round($order->getTotal(), $order->getPrecisionOperacion()));
            $stmt->bindValue('totivairpf', round($order->getTotalIVA(), $order->getPrecisionOperacion()));
            $stmt->bindValue('totivairpf1', round($order->getBaseIVA1(), $order->getPrecisionOperacion()));
            $stmt->bindValue('totivairpf2', round($order->getBaseIVA1(), $order->getPrecisionOperacion()));
            $stmt->bindValue('totivairpf3', round($order->getBaseIVA3(), $order->getPrecisionOperacion()));
            $stmt->bindValue('totaldecirpf', round($order->getTotal(), $order->getPrecisionOperacion()));
            $stmt->bindValue('exentoiva', $exento);
            $stmt->bindValue('fcoste', 1, \PDO::PARAM_INT);
            $stmt->bindValue('eurotodivi', 1, \PDO::PARAM_INT);
            $stmt->bindValue('eurotomone', 1, \PDO::PARAM_INT);
            $stmt->bindValue('f_prevista', $order->getFecha());
            $stmt->bindValue('coment_1', $pedidoPorCamion);
            $stmt->bindValue('des1', $descuentosEnCascada[0]);
            $stmt->bindValue('des2', $descuentosEnCascada[1]);
            $stmt->bindValue('des3', $descuentosEnCascada[2]);
            $stmt->bindValue('des4', $descuentosEnCascada[3]);
            $stmt->bindValue('des5', $descuentosEnCascada[4]);
            $stmt->bindValue('des6', $descuentosEnCascada[5]);

            $stmt->execute();

            $sqlMovimiento = "
                INSERT INTO DATMO03 (
                    FECHA,
                    NUMERO,
                    POSICION,
                    CODART,
                    UNIDADES,
                    IMPORTE,
                    TOTIMPORTE,
                    TOTAL,
                    IVA,
                    DENOMINACI,
                    TARIFA,
                    DESCUENTO
                ) VALUES (
                    :fecha,
                    :numero,
                    :posicion,
                    :codart,
                    :unidades,
                    :importe,
                    :totimporte,
                    :total,
                    :iva,
                    :denominaci,
                    :tarifa,
                    :descuento
                )
            ";

            $stmtMovement = $this->aquaDatabase->prepare($sqlMovimiento);

            /** @var ProposalProduct $producto */
            foreach ($order->getProductos() as $producto) {
                $posicion = array_shift($positions);

                $stmtMovement->bindValue(':fecha', $order->getFecha());
                $stmtMovement->bindValue(':numero', $operation, \PDO::PARAM_INT);
                $stmtMovement->bindValue(':posicion', $posicion, \PDO::PARAM_INT);
                $stmtMovement->bindValue(':codart', $producto->getProductCode()->getSku());
                $stmtMovement->bindValue(':unidades', $producto->getUnits(), \PDO::PARAM_INT);
                $stmtMovement->bindValue(':importe', round($producto->getUnitPriceTaxExcl(), $order->getPrecisionLinea()));
                $stmtMovement->bindValue(':totimporte', round($producto->getTotalPriceTaxExcl(), $order->getPrecisionLinea()));
                $stmtMovement->bindValue(':total', round($producto->getTotalPriceTaxExcl(), $order->getPrecisionLinea()));
                $stmtMovement->bindValue(':iva', $producto->getTax()->getAquaType());
                $stmtMovement->bindValue(':denominaci', mb_substr($producto->getName(), 0, 100));
                $stmtMovement->bindValue(':tarifa', round($producto->getTariff(), $order->getPrecisionLinea()));
                $stmtMovement->bindValue(':descuento', $producto->getDiscount());

                $stmtMovement->execute();
            }

            $this->aquaDatabase->commit();

            $order->setDocNumber($docNumber);

        } catch (\PDOException $exception) {
            $this->aquaDatabase->rollBack();

            throw new KpySqlException(
                $exception->getMessage(),
                __METHOD__,
                $this->aquaDatabase->getLastSql(),
                $this->aquaDatabase->getSqlError()
            );
        }
    }
}
