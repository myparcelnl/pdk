<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItemFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of CustomsDeclarationItemCollection
 * @method CustomsDeclarationItemCollection make()
 */
final class CustomsDeclarationItemCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return CustomsDeclarationItemCollection::class;
    }

    protected function getModelFactory(): string
    {
        return CustomsDeclarationItemFactory::class;
    }
}
