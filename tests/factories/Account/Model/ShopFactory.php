<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Account\Collection\ShopCarrierConfigurationCollection;
use MyParcelNL\Pdk\Account\Collection\ShopCollection;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollectionFactory;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Carrier\Model\CarrierFactory;
use MyParcelNL\Pdk\Facade\Platform as PlatformFacade;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of Shop
 * @method Shop make()
 * @method $this withAccountId(int $accountId)
 * @method $this withBilling(array $billing)
 * @method $this withCarrierConfigurations(array|ShopCarrierConfigurationCollection|ShopCarrierConfigurationFactory[] $carrierConfigurations)
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
    public function getModel(): string
    {
        return Shop::class;
    }

    protected function createDefault(): FactoryInterface
    {
        return $this->withCarriers(
            factory(CarrierCollection::class)->push(
                factory(Carrier::class)
                    ->withExternalIdentifier(PlatformFacade::get('defaultCarrier'))
                    ->withCapabilities(factory(CarrierCapabilities::class)->withEverything())
            )
        );
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
