<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Collection;

use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethodFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of PdkShippingMethodCollection
 * @method PdkShippingMethodCollection make()
 */
final class PdkShippingMethodCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return PdkShippingMethodCollection::class;
    }

    protected function getModelFactory(): string
    {
        return PdkShippingMethodFactory::class;
    }
}
