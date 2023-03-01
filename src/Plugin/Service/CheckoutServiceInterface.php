<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

interface CheckoutServiceInterface
{
    /**
     * Retrieve a key => label collection of all available positions to render the delivery options in.
     *
     * @return array<string, string>
     */
    public function getPositions(): array;
}
