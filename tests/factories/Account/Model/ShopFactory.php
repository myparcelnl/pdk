<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Account\Collection\ShopCollection;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollectionFactory;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierFactory;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of Shop
 * @method Shop make()
 * @method $this withAccountId(int $accountId)
 * @method $this withBilling(array $billing)
 * @method $this withCarriers(array|CarrierCollection|CarrierFactory[]|CarrierCollectionFactory $carriers)
 * @method $this withDeliveryAddress(array $deliveryAddress)
 * @method $this withGeneralSettings(array $generalSettings)
 * @method $this withHidden(bool $hidden)
 * @method $this withId(int $id)
 * @method $this withName(string $name)
 * @method $this withPlatformId(int $platformId)
 * @method $this withReturn(array $return)
 * @method $this withShipmentOptions(array $shipmentOptions)
 * @method $this withTrackTrace(array $trackTrace)
 */
final class ShopFactory extends AbstractModelFactory
{
    /**
     * Add a single carrier to the shop's carriers
     *
     * @param  Carrier|CarrierFactory $carrier
     * @return $this
     */
    public function addCarrier($carrier): self
    {
        $carriers = $this->attributes['carriers'] ?? factory(CarrierCollection::class);

        if ($carriers instanceof CarrierCollectionFactory) {
            $carriers->push($carrier);
        } else {
            // If it's already a collection, convert to factory
            $carriers = factory(CarrierCollection::class)->push($carrier);
        }

        return $this->withCarriers($carriers);
    }

    /**
     * Add all known carriers with all available capabilities as a default for testing.
     *
     * @return $this
     */
    public function withAllCarriers(): self
    {
        $carrierNames = RefCapabilitiesSharedCarrierV2::getAllowableEnumValues();
        $carrierFactories = factory(CarrierCollection::class);

        foreach ($carrierNames as $name) {
            $carrierFactories->push(factory(Carrier::class)->withAllCapabilities($name));
        }

        return $this->withCarriers($carrierFactories);
    }

    public function getModel(): string
    {
        return Shop::class;
    }

    protected function createDefault(): FactoryInterface
    {
        return $this->withAllCarriers();
    }

    /**
     * @param  \MyParcelNL\Pdk\Account\Model\Shop $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        factory(Account::class)
            ->withShops(factory(ShopCollection::class)->push($model))
            ->store();
    }
}
