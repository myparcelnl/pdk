<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Collection;

use MyParcelNL\Pdk\Account\Model\ShopFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of ShopCollection
 * @method ShopCollection make()
 */
final class ShopCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return ShopCollection::class;
    }

    protected function getModelFactory(): string
    {
        return ShopFactory::class;
    }
}
