<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Collection;

use MyParcelNL\Pdk\Fulfilment\Model\ShippedItemFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of ShippedItemCollection
 * @method ShippedItemCollection make()
 */
final class ShippedItemCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return ShippedItemCollection::class;
    }

    protected function getModelFactory(): string
    {
        return ShippedItemFactory::class;
    }
}
