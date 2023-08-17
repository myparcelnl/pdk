<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Collection;

use MyParcelNL\Pdk\Account\Model\ShopCarrierConfigurationFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of ShopCarrierConfigurationCollection
 * @method ShopCarrierConfigurationCollection make()
 */
final class ShopCarrierConfigurationCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return ShopCarrierConfigurationCollection::class;
    }

    protected function getModelFactory(): string
    {
        return ShopCarrierConfigurationFactory::class;
    }
}
