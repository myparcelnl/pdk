<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of Settings
 * @method Settings make()
 * @method $this withAccount(array|AccountSettings|AccountSettingsFactory $account)
 * @method $this withCheckout(array|CheckoutSettings|CheckoutSettingsFactory $checkout)
 * @method $this withCustoms(array|CustomsSettings|CustomsSettingsFactory $customs)
 * @method $this withGeneral(array|GeneralSettings|GeneralSettingsFactory $general)
 * @method $this withLabel(array|LabelSettings|LabelSettingsFactory $label)
 * @method $this withOrder(array|OrderSettings|OrderSettingsFactory $order)
 */
final class SettingsFactory extends AbstractSettingsModelFactory
{
    public function getModel(): string
    {
        return Settings::class;
    }

    /**
     * @param  string                                       $carrier
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarrier(string $carrier, $data): self
    {
        if (! isset($this->attributes['carrier'])) {
            $this->attributes['carrier'] = factory(SettingsModelCollection::class);
        }

        $value = is_array($data)
            ? factory(CarrierSettings::class, $carrier)
                ->withId($carrier)
                ->with($data)
            : $data + ['id' => $carrier];

        $this->attributes['carrier']->put($carrier, $value);

        return $this;
    }

    /**
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarrierBpost($data): self
    {
        return $this->withCarrier(Carrier::CARRIER_BPOST_NAME, $data);
    }

    /**
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarrierDhlEuroplus($data): self
    {
        return $this->withCarrier(Carrier::CARRIER_DHL_EUROPLUS_NAME, $data);
    }

    /**
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarrierDhlForYou($data): self
    {
        return $this->withCarrier(Carrier::CARRIER_DHL_FOR_YOU_NAME, $data);
    }

    /**
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarrierDhlParcelConnect($data): self
    {
        return $this->withCarrier(Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME, $data);
    }

    /**
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarrierDpd($data): self
    {
        return $this->withCarrier(Carrier::CARRIER_DPD_NAME, $data);
    }

    /**
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarrierPostNl($data): self
    {
        return $this->withCarrier(Carrier::CARRIER_POSTNL_NAME, $data);
    }

    /**
     * @param  T $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        Pdk::get(SettingsRepositoryInterface::class)
            ->storeAllSettings($model);
    }
}

