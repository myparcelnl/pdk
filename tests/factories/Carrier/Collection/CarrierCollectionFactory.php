<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Collection;

use MyParcelNL\Pdk\Carrier\Model\CarrierFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of CarrierCollection
 * @method CarrierCollection make()
 */
final class CarrierCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return CarrierCollection::class;
    }

    protected function getModelFactory(): string
    {
        return CarrierFactory::class;
    }
}
