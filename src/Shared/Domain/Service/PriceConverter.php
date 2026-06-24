<?php

namespace App\Shared\Domain\Service;

class PriceConverter
{
    protected int $decimals;
    protected string $multiplier;

    /**
     * @param int $decimals Cantidad de decimales a tener en cuenta (por defecto 2).
     */
    public function __construct(int $decimals = 2)
    {
        $this->setDecimals($decimals);
    }

    private function setDecimals(int $decimals): void
    {
        $this->decimals = $decimals;
        // Creamos el multiplicador basado en los decimales (ej: 2 decimales -> "100")
        $this->multiplier = bcpow('10', (string)$decimals);
    }

    /**
     * Convierte un precio decimal (string de la BD) a un entero (céntimos/unidades mínimas).
     * Ejemplo: "125.50" -> 12550
     *
     * @param string $decimalPrice
     * @return int
     */
    public function toInteger(string $decimalPrice): int
    {
        // Limpiamos espacios o formatos extraños por seguridad
        $decimalPrice = trim($decimalPrice);

        // Multiplicamos usando BCMath para asegurar precisión absoluta
        // El tercer parámetro '0' asegura que el resultado de la multiplicación no tenga decimales
        $result = bcmul($decimalPrice, $this->multiplier, 0);

        return (int)$result;
    }

    /**
     * Convierte un precio entero (céntimos) a una cadena decimal para guardar en la BD.
     * Ejemplo: 12550 -> 125.50
     */
    public function toDecimal(int $integerPrice): float
    {
        // Dividimos el entero por el multiplicador aplicando los decimales configurados
        return (float)bcdiv((string)$integerPrice, $this->multiplier, $this->decimals);
    }
}
