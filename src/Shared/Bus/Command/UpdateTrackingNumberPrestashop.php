<?php

namespace App\Shared\Bus\Command;


use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.shared.command')]
readonly class UpdateTrackingNumberPrestashop implements KpyCommandInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.shared.command.update_trackingnumber_prestashop';
    }

    public function execute(array $params = []): bool
    {
        if (!isset($params['orderId'], $params['trackingNumber'])) {
            throw new \InvalidArgumentException('Order id and tracking number are required.');
        }

        return $this->kompyDatabase->execute(
            "UPDATE ps_order_carrier SET tracking_number = '{$params['trackingNumber']}' WHERE id_order = {$params['orderId']}"
        );
    }
}
