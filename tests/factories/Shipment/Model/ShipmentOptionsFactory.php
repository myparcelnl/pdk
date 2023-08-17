<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of ShipmentOptions
 * @method ShipmentOptions make()
 * @method $this withAgeCheck(bool $ageCheck)
 * @method $this withHideSender(bool $hideSender)
 * @method $this withInsurance(int $insurance)
 * @method $this withLabelDescription(string $labelDescription)
 * @method $this withLargeFormat(bool $largeFormat)
 * @method $this withOnlyRecipient(bool $onlyRecipient)
 * @method $this withReturn(bool $return)
 * @method $this withSameDayDelivery(bool $sameDayDelivery)
 * @method $this withSignature(bool $signature)
 */
final class ShipmentOptionsFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return ShipmentOptions::class;
    }

    public function withAllOptions(): self
    {
        return $this
            ->withAgeCheck(true)
            ->withHideSender(true)
            ->withInsurance(100)
            ->withLabelDescription('test')
            ->withLargeFormat(true)
            ->withOnlyRecipient(true)
            ->withReturn(true)
            ->withSameDayDelivery(true)
            ->withSignature(true);
    }
}
