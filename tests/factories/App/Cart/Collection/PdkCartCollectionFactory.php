<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Collection;

use MyParcelNL\Pdk\App\Cart\Model\PdkCartFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of PdkCartCollection
 * @method PdkCartCollection make()
 */
final class PdkCartCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return PdkCartCollection::class;
    }

    protected function getModelFactory(): string
    {
        return PdkCartFactory::class;
    }
}
