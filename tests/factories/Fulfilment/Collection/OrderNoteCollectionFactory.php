<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Collection;

use MyParcelNL\Pdk\Fulfilment\Model\OrderNoteFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of OrderNoteCollection
 * @method OrderNoteCollection make()
 */
final class OrderNoteCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return OrderNoteCollection::class;
    }

    protected function getModelFactory(): string
    {
        return OrderNoteFactory::class;
    }
}
