<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Shipment\Model\DropOffDayFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of DropOffDayCollection
 * @method DropOffDayCollection make()
 */
final class DropOffDayCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return DropOffDayCollection::class;
    }

    protected function getModelFactory(): string
    {
        return DropOffDayFactory::class;
    }
}
