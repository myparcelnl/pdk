<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of CarrierCapabilities
 * @method CarrierCapabilities make()
 * @method $this withDeliveryTypes(string[] $deliveryTypes)
 * @method $this withFeatures(array $features)
 * @method $this withPackageTypes(string[] $packageTypes)
 * @method $this withShipmentOptions(array $shipmentOptions)
 */
final class CarrierCapabilitiesFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return CarrierCapabilities::class;
    }
}
