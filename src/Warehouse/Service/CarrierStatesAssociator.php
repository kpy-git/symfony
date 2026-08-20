<?php

namespace App\Warehouse\Service;

use App\Shared\Domain\Exception\KpyException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class CarrierStatesAssociator
{
    private array $configByCarrier;

    private string $configFile;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        string $srcDir,
        #[Autowire('%env(SHIPPED_OS)%')]
        private string $shippedOS,
    )
    {
        $this->configFile = $srcDir . DIRECTORY_SEPARATOR . 'config/tracking/config.json';

        if (!is_readable($this->configFile)) {
            throw new KpyException('No existe el fichero de configuración para el tracking de los transportistas');
        }

        $this->configByCarrier = json_decode(file_get_contents($this->configFile), true);
    }

    public function getStatesForSearchingByCarrier(string $carrier): array
    {
        if (!isset($this->configByCarrier[$carrier])) {
            throw new KpyException('Configuración no encontrada para el transportista ' . $carrier . ' [' . $this->configFile . ']');
        }

        return $this->configByCarrier[$carrier]['states_for_searching'];
    }

    public function getOwnStateBy(string $carrier, string $state): int
    {
        if (!isset($this->configByCarrier[$carrier])) {
            throw new KpyException('Configuración no encontrada para el transportista ' . $carrier . ' [' . $this->configFile . ']');
        }

        // cualquier estado diferente a los que tenemos asociados dará como resultado ENVIADO
        return $this->configByCarrier[$carrier]['matching_states'][$state] ?? $this->shippedOS;
    }
}
