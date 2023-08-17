<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Fulfilment\Collection\ShippedItemCollection;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of OrderShipment
 * @method OrderShipment make()
 * @method $this withExternalShipmentIdentifier(string $externalShipmentIdentifier)
 * @method $this withShipment(int $shipment)
 * @method $this withShipmentId(int $shipmentId)
 * @method $this withShippedItems(ShippedItemCollection|ShippedItemFactory[] $shippedItems)
 * @method $this withUuid(string $uuid)
 */
final class OrderShipmentFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return OrderShipment::class;
    }
}
