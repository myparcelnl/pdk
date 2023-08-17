<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderNoteFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of PdkOrderNoteCollection
 * @method PdkOrderNoteCollection make()
 */
final class PdkOrderNoteCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return PdkOrderNoteCollection::class;
    }

    protected function getModelFactory(): string
    {
        return PdkOrderNoteFactory::class;
    }
}
