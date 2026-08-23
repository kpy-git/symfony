<?php

namespace App\Warehouse\Domain\Carrier\MRW;

class SeguimientoDTO implements \JsonSerializable
{
    public string $Estado;
    public string $EstadoDescripcion;
    public ?string $FechaEntrega;
    public ?string $HoraEntrega;
    public int $Intentos;
    public string $NumAlbaran;
    public \DateTimeImmutable $Publicado;
    public string $Referencia;

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }
}
