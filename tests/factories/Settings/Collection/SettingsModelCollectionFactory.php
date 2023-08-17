<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Collection;

use MyParcelNL\Pdk\Settings\Model\AbstractSettingsModelFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of SettingsModelCollection
 * @method SettingsModelCollection make()
 */
final class SettingsModelCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return SettingsModelCollection::class;
    }

    protected function getModelFactory(): string
    {
        return AbstractSettingsModelFactory::class;
    }
}
