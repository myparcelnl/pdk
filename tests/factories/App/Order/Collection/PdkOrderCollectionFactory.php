<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of PdkOrderCollection
 * @method PdkOrderCollection make()
 */
final class PdkOrderCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return PdkOrderCollection::class;
    }

    protected function getModelFactory(): string
    {
        return PdkOrderFactory::class;
    }
}
