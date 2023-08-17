<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Collection;

use MyParcelNL\Pdk\App\Cart\Model\PdkCartFeeFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of PdkCartFeeCollection
 * @method PdkCartFeeCollection make()
 */
final class PdkCartFeeCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return PdkCartFeeCollection::class;
    }

    protected function getModelFactory(): string
    {
        return PdkCartFeeFactory::class;
    }
}
