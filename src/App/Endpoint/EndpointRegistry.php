<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Endpoint\Handler\GetDeliveryOptionsEndpoint;

/**
 * Type-safe registry for PDK endpoint identifiers and handler mappings.
 *
 * Maps endpoint constants to their respective handler classes for type-safe routing.
 * Use factory methods like `EndpointRegistry::deliveryOptions()` for type safety.
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
     * @var string
     */
    private $handlerClass;

    /**
     * Private constructor to ensure only valid handler classes can be created.
     */
    private function __construct(string $handlerClass)
    {
        $this->handlerClass = $handlerClass;
    }

    /**
     * Create DELIVERY_OPTIONS endpoint registry entry.
     */
    public static function deliveryOptions(): self
    {
        return new self(self::DELIVERY_OPTIONS);
    }

    /**
     * Create an endpoint registry entry from a handler class name (with validation).
     */
    public static function fromClass(string $handlerClass): self
    {
        if (!self::isValidClass($handlerClass)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid endpoint handler class "%s". Valid classes are: %s',
                $handlerClass,
                implode(', ', self::getValidClasses())
            ));
        }

        return new self($handlerClass);
    }

    /**
     * Get the handler class name of this endpoint.
     */
    public function getHandlerClass(): string
    {
        return $this->handlerClass;
    }

    /**
     * Get all valid endpoint handler classes.
     */
    public static function getValidClasses(): array
    {
        return [
            self::DELIVERY_OPTIONS,
            // Add future endpoints here
        ];
    }

    /**
     * Check if a handler class is valid.
     */
    private static function isValidClass(string $handlerClass): bool
    {
        return in_array($handlerClass, self::getValidClasses(), true);
    }

    /**
     * Compare this endpoint registry entry with another.
     */
    public function equals(EndpointRegistry $other): bool
    {
        return $this->handlerClass === $other->handlerClass;
    }

    /**
     * Get string representation.
     */
    public function __toString(): string
    {
        return $this->handlerClass;
    }

    /**
     * Get array representation for debugging.
     */
    public function toArray(): array
    {
        return [
            'handlerClass' => $this->handlerClass,
        ];
    }
}
