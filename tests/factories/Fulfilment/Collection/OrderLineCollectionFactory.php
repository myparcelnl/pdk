<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Collection;

use MyParcelNL\Pdk\Fulfilment\Model\OrderLineFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of OrderLineCollection
 * @method OrderLineCollection make()
 */
final class OrderLineCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return OrderLineCollection::class;
    }

    protected function getModelFactory(): string
    {
        return OrderLineFactory::class;
    }
}
