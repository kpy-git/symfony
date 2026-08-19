<?php

namespace App\Shared\Bus\Command;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class KpyCommandBus
{
    protected array $commands = [];

    /** @var KpyCommandInterface[] $commands */
    public function __construct(
        #[AutowireIterator('kpy.shared.command')]
        iterable $commands)
    {
        foreach ($commands as $command) {
            $this->commands[$command->getName()] = $command;
        }
    }

    /**
     * @throws KpyCommandNotFoundException
     */
    public function execute(string $commandName, array $params = []): mixed
    {
        if (!isset($this->commands[$commandName])) {
            throw new KpyCommandNotFoundException('Command not found: ' . $commandName);
        }

        return $this->commands[$commandName]->execute($params);
    }
}
