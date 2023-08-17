<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Collection;

use MyParcelNL\Pdk\Context\Model\ProductDataContextFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of ProductDataContextCollection
 * @method ProductDataContextCollection make()
 */
final class ProductDataContextCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return ProductDataContextCollection::class;
    }

    protected function getModelFactory(): string
    {
        return ProductDataContextFactory::class;
    }
}
