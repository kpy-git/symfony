<?php

namespace App\Shared\Bus\Query;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class KpyQueryBus
{
    protected array $queries;

    /** @var KpyQueryInterface[] $queries */
    public function __construct(#[AutowireIterator('kpy.shared.query')] iterable $queries)
    {
        foreach ($queries as $query) {
            $this->queries[$query->getName()] = $query;
        }
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function fetch(string $query, array $params = []): mixed
    {
        if (!isset($this->queries[$query])) {
            throw new KpyQueryNotFoundException('Query not found: ' . $query);
        }

        return $this->queries[$query]->fetch($params);
    }
}
