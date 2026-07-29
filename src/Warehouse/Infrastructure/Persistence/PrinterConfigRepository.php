<?php

namespace App\Warehouse\Infrastructure\Persistence;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

class PrinterConfigRepository
{

    private string $configFilePath;

    public function __construct(
        #[Autowire('%.kernel.config_dir%')]
        string $configPath,
        private readonly Filesystem $filesystem,
    )
    {
        $this->configFilePath = $configPath . '/qz/printers.json';

    }

    public function getConfig(string $warehouse): array
    {
        if (!$this->filesystem->exists($this->configFilePath)) {
            return [];
        }

        $config = json_decode($this->filesystem->readFile($this->configFilePath), true);

        return $config[$warehouse] ?? [];
    }
}
