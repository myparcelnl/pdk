<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Collection;

use MyParcelNL\Pdk\Context\Model\OrderDataContextFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of OrderDataContextCollection
 * @method OrderDataContextCollection make()
 */
final class OrderDataContextCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return OrderDataContextCollection::class;
    }

    protected function getModelFactory(): string
    {
        return OrderDataContextFactory::class;
    }
}
