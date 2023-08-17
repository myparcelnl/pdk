<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollectionFactory;

/**
 * @template T of Settings
 * @method Settings make()
 * @method $this withAccount(AccountSettings|AccountSettingsFactory $account)
 * @method $this withCarrier(CarrierSettingsFactory[]|SettingsModelCollectionFactory|SettingsModelCollection[]|CarrierSettings[] $carrier)
 * @method $this withCheckout(CheckoutSettings|CheckoutSettingsFactory $checkout)
 * @method $this withCustoms(CustomsSettings|CustomsSettingsFactory $customs)
 * @method $this withGeneral(GeneralSettings|GeneralSettingsFactory $general)
 * @method $this withLabel(LabelSettings|LabelSettingsFactory $label)
 * @method $this withOrder(OrderSettings|OrderSettingsFactory $order)
 */
final class SettingsFactory extends AbstractSettingsModelFactory
{
    public function getModel(): string
    {
        return Settings::class;
    }
}
