<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Contract;

interface OrderStatusServiceInterface
{
    /**
     * Retrieve a key => translated label collection of all available order statuses.
     *
     * @return array<string, string>
     */
    public function all(): array;
}
