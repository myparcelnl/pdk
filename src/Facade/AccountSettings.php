<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Account\Service\AccountSettingsService;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection;

/**
 * @method static null|Account getAccount()
 * @method static CarrierOptionsCollection getCarrierOptions()
 * @method static null|Shop getShop()
 * @implements \MyParcelNL\Pdk\Account\Service\AccountSettingsService
 */
class AccountSettings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AccountSettingsService::class;
    }
}