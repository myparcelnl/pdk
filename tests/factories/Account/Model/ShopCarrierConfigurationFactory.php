<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Account\Collection\ShopCarrierConfigurationCollection;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of ShopCarrierConfiguration
 * @method ShopCarrierConfiguration make()
 * @method $this withCarrier(string $carrier)
 * @method $this withDefaultCutoffTime(string $defaultCutoffTime)
 * @method $this withDefaultDropOffPoint(string $defaultDropOffPoint)
 * @method $this withDefaultDropOffPointIdentifier(string $defaultDropOffPointIdentifier)
 * @method $this withMondayCutoffTime(string $mondayCutoffTime)
 */
final class ShopCarrierConfigurationFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return ShopCarrierConfiguration::class;
    }

    /**
     * @param  \MyParcelNL\Pdk\Account\Model\ShopCarrierConfiguration $model
     */
    protected function save(Model $model): void
    {
        factory(Shop::class)
            ->withCarrierConfigurations(factory(ShopCarrierConfigurationCollection::class)->push($model))
            ->store();
    }
}
