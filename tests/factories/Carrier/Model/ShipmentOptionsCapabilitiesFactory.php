<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of ShipmentOptionsCapabilities
 * @method ShipmentOptionsCapabilities make()
 * @method $this withAgeCheck(Capability|CapabilityFactory $ageCheck)
 * @method $this withDropOffAtPostalPoint(Capability|CapabilityFactory $dropOffAtPostalPoint)
 * @method $this withInsurance(Capability|CapabilityFactory $insurance)
 * @method $this withLabelDescription(Capability|CapabilityFactory $labelDescription)
 * @method $this withLargeFormat(Capability|CapabilityFactory $largeFormat)
 * @method $this withOnlyRecipient(Capability|CapabilityFactory $onlyRecipient)
 * @method $this withReturn(Capability|CapabilityFactory $return)
 * @method $this withSameDayDelivery(Capability|CapabilityFactory $sameDayDelivery)
 * @method $this withSaturdayDelivery(Capability|CapabilityFactory $saturdayDelivery)
 * @method $this withSignature(Capability|CapabilityFactory $signature)
 */
final class ShipmentOptionsCapabilitiesFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return ShipmentOptionsCapabilities::class;
    }
}
