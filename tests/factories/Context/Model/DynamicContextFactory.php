<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\AccountFactory;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Account\Model\ShopFactory;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\CarrierFactory;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Settings\Model\SettingsFactory;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of DynamicContext
 * @method DynamicContext make()
 * @method $this withAccount(Account|AccountFactory $account)
 * @method $this withCarriers(CarrierCollection|CarrierFactory[]|CarrierFactory[] $carriers)
 * @method $this withPluginSettings(Settings|SettingsFactory $pluginSettings)
 * @method $this withShop(Shop|ShopFactory $shop)
 */
final class DynamicContextFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return DynamicContext::class;
    }
}
