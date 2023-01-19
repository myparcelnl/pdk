<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

interface OrderStatusServiceInterface
{
    /**
     * Retrieve a key => label collection of all available order statuses.
     *
     * @return array<string, string>
     */
    public function all(): array;
}
