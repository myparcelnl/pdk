<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Collection;

use MyParcelNL\Pdk\Fulfilment\Model\OrderFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of OrderCollection
 * @method OrderCollection make()
 */
final class OrderCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return OrderCollection::class;
    }

    protected function getModelFactory(): string
    {
        return OrderFactory::class;
    }
}
