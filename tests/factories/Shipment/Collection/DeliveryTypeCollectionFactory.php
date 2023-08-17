<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Shipment\Model\DeliveryTypeFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of DeliveryTypeCollection
 * @method DeliveryTypeCollection make()
 */
final class DeliveryTypeCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return DeliveryTypeCollection::class;
    }

    protected function getModelFactory(): string
    {
        return DeliveryTypeFactory::class;
    }
}
