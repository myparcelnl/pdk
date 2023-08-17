<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of ShippedItem
 * @method ShippedItem make()
 * @method $this withOrderLineIdentifier(string $orderLineIdentifier)
 * @method $this withQuantity(int $quantity)
 */
final class ShippedItemFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return ShippedItem::class;
    }
}
