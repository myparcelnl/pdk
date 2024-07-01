<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;

/**
 * @method static null|Account getAccount()
 * @method static CarrierCollection getCarrierOptions()
 * @method static CarrierCollection getCarriers()
 * @method static null|Shop getShop()
 * @method static bool hasCarrier(string $name)
 * @method static bool hasCarrierSmallPackageContract()
 * @method static bool hasCarrierMailContract()
 * @method static bool hasSubscriptionFeature(string $feature)
 * @method static bool hasTaxFields()
 * @method static bool usesOrderMode()
 * @see \MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface
 */
final class AccountSettings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AccountSettingsServiceInterface::class;
    }
}
