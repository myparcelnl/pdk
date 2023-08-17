<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

abstract class AbstractSettingsModelFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return AbstractSettingsModel::class;
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        Pdk::get(SettingsRepositoryInterface::class)
            ->storeSettings($model);
    }
}
