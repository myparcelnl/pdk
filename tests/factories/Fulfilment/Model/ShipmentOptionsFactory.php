<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of ShipmentOptions
 * @method ShipmentOptions make()
 * @method $this withAgeCheck(bool $ageCheck)
 * @method $this withCollect(bool $collect)
 * @method $this withCooledDelivery(bool $cooledDelivery)
 * @method $this withDeliveryDate(string $deliveryDate)
 * @method $this withDeliveryType(int $deliveryType)
 * @method $this withDropOffAtPostalPoint(bool $dropOffAtPostalPoint)
 * @method $this withHideSender(bool $hideSender)
 * @method $this withInsurance(int $insurance)
 * @method $this withLabelDescription(string $labelDescription)
 * @method $this withLargeFormat(bool $largeFormat)
 * @method $this withOnlyRecipient(bool $onlyRecipient)
 * @method $this withPriorityDelivery(bool $priorityDelivery)
 * @method $this withPackageType(int $packageType)
 * @method $this withReturn(bool $return)
 * @method $this withSameDayDelivery(bool $sameDayDelivery)
 * @method $this withSaturdayDelivery(bool $saturdayDelivery)
 * @method $this withSignature(bool $signature)
 */
final class ShipmentOptionsFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return ShipmentOptions::class;
    }
}
