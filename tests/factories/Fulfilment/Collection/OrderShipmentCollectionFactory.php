<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Collection;

use MyParcelNL\Pdk\Fulfilment\Model\OrderShipmentFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of OrderShipmentCollection
 * @method OrderShipmentCollection make()
 */
final class OrderShipmentCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return OrderShipmentCollection::class;
    }

    protected function getModelFactory(): string
    {
        return OrderShipmentFactory::class;
    }
}
