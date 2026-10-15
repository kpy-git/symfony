<?php

namespace App\Shared\Bus\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.shared.query')]
readonly class KpyPymAffectedOrderQuery implements KpyQueryInterface
{
    public function __construct(
        private DatabaseInterface $kompyDatabase,
        private int $pymAffectedOS
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.shared.query.pym_affedted_order';
    }

    public function fetch(array $params = []): int
    {
        return $this->kompyDatabase->getValue(
            "select exists(select 1 from ps_order_history where id_order={$params['id_order']} and id_order_state={$this->pymAffectedOS}) as `exists`"
        );
    }
}
