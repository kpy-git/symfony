<?php

namespace App\Warehouse\Domain\Carrier\MRW;

class TransmEnvioResultDTO
{
    public int $Estado;
    public string $Mensaje;
    public string $NumeroSolicitud;
    public ?string $NumeroEnvio = null;
    public ?string $Url = null;
}
