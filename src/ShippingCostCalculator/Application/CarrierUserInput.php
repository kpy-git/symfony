<?php

namespace App\ShippingCostCalculator\Application;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\Ask;

class CarrierUserInput
{
    #[Argument]
    #[Ask('Nombre')]
    public string $name;

    #[Argument]
    #[Ask('ID del servicio en AQUA')]
    public string $idServiceAqua;

    #[Argument]
    #[Ask('¿permite expediciones multibulto?')]
    public bool $multiparcelAllowed;

    #[Argument]
    #[Ask('Peso máximo por expedición')]
    public float $maxShippingWeight;

    #[Argument]
    #[Ask('Peso máximo por bulto')]
    public float $maxParcelWeight;

}
