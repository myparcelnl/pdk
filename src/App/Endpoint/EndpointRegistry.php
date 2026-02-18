<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint;

use MyParcelNL\Pdk\App\Endpoint\Handler\GetDeliveryOptionsEndpoint;

/**
 * Type-safe registry for PDK endpoint identifiers and handler mappings.
 *
 * Maps endpoint constants to their respective handler classes for type-safe routing.
 * TODO: Refactor to enum when PHP 8.1+ is minimum requirement.
 *
 * @since 3.1.0
 */
class EndpointRegistry
{
    public const DELIVERY_OPTIONS = GetDeliveryOptionsEndpoint::class;

    // Future endpoints can be added here:
    // public const SHIPMENTS = GetShipmentsEndpoint::class;
    // public const ORDERS = GetOrdersEndpoint::class;

    /**
     * Get all  endpoint handler classes.
     */
    public static function all(): array
    {
        return [
            self::DELIVERY_OPTIONS,
            // Add future endpoints here
        ];
    }
}
