<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkProductFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of PdkProductCollection
 * @method PdkProductCollection make()
 */
final class PdkProductCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return PdkProductCollection::class;
    }

    protected function getModelFactory(): string
    {
        return PdkProductFactory::class;
    }
}
