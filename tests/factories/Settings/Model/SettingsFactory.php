<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of Settings
 * @method $this withAccount(array|AccountSettings|AccountSettingsFactory $account)
 * @method $this withCheckout(array|CheckoutSettings|CheckoutSettingsFactory $checkout)
 * @method $this withCustoms(array|CustomsSettings|CustomsSettingsFactory $customs)
 * @method $this withLabel(array|LabelSettings|LabelSettingsFactory $label)
 * @method $this withOrder(array|OrderSettings|OrderSettingsFactory $order)
 */
final class SettingsFactory extends AbstractModelFactory
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
    public function withCarrier(string $carrier, $data = null): self
    {
        // The "carrier" attribute refers not to a Carrier model but to a collection of CarrierSetting models.
        if (! isset($this->attributes['carrier'])) {
            $this->attributes['carrier'] = factory(SettingsModelCollection::class);
        }

        if ($data instanceof FactoryInterface) {
            $value = $data->withCarrier($carrier);
        } elseif (is_array($data) || null === $data) {
            $value = factory(CarrierSettings::class, $carrier)
                ->withId($carrier)
                ->with($data ?? []);
        } elseif ($data instanceof Model) {
            $value = $data->fill(['id' => $carrier]);
        } else {
            throw new InvalidArgumentException('Invalid data type');
        }

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

        return $this->withCarrier(RefCapabilitiesSharedCarrierV2::BPOST, $data);
    }

    /**
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarrierDhlEuroplus($data): self
    {
        return $this->withCarrier(RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS, $data);
    }

    /**
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarrierDhlForYou($data): self
    {
        return $this->withCarrier(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU, $data);
    }

    /**
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarrierDhlParcelConnect($data): self
    {
        return $this->withCarrier(RefCapabilitiesSharedCarrierV2::DHL_PARCEL_CONNECT, $data);
    }

    /**
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarrierDpd($data): self
    {
        return $this->withCarrier(RefCapabilitiesSharedCarrierV2::DPD, $data);
    }

    /**
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarrierPostNl($data): self
    {
        return $this->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL, $data);
    }

    /**
     * @param  string[]                                     $carriers
     * @param  array|CarrierSettings|CarrierSettingsFactory $data
     *
     * @return $this
     */
    public function withCarriers(array $carriers, $data = null): self
    {
        foreach ($carriers as $carrier) {
            $this->withCarrier($carrier, $data);
        }

        return $this;
    }

    /**
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     */
    protected function createDefault(): FactoryInterface
    {
        // @TODO replace with dynamic fetch from shipping rules in the future
        $propositionService = Pdk::get(PropositionService::class);
        return $this->withCarrier($propositionService->getDefaultCarrier()->carrier);
    }

    /**
     * @param  T $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository $repository */
        $repository = Pdk::get(PdkSettingsRepositoryInterface::class);

        $repository->storeAllSettings($model);
    }
}
