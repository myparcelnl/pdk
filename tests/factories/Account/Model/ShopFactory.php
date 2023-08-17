<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Account\Collection\ShopCarrierConfigurationCollection;
use MyParcelNL\Pdk\Account\Collection\ShopCollection;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\CarrierFactory;
use MyParcelNL\Pdk\Tests\Factory\Concern\HasIncrementingId;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of Shop
 * @method Shop make()
 * @method $this withAccountId(int $accountId)
 * @method $this withBilling($billing)
 * @method $this withCarrierConfigurations(ShopCarrierConfigurationCollection|ShopCarrierConfigurationFactory[] $carrierConfigurations)
 * @method $this withCarriers(CarrierCollection|CarrierFactory[] $carriers)
 * @method $this withDeliveryAddress($deliveryAddress)
 * @method $this withGeneralSettings($generalSettings)
 * @method $this withHidden(bool $hidden)
 * @method $this withId(int $id)
 * @method $this withName(string $name)
 * @method $this withPlatformId(int $platformId)
 * @method $this withReturn($return)
 * @method $this withShipmentOptions($shipmentOptions)
 * @method $this withTrackTrace(array $trackTrace)
 */
final class ShopFactory extends AbstractModelFactory
{
    use HasIncrementingId;

    public function getModel(): string
    {
        return Shop::class;
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
