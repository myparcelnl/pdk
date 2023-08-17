<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderLineFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of PdkOrderLineCollection
 * @method PdkOrderLineCollection make()
 */
final class PdkOrderLineCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return PdkOrderLineCollection::class;
    }

    protected function getModelFactory(): string
    {
        return PdkOrderLineFactory::class;
    }
}
