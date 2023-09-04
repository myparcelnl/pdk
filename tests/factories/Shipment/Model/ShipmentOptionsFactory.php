<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * @template T of ShipmentOptions
 * @method ShipmentOptions make()
 * @method $this withAgeCheck(int $ageCheck)
 * @method $this withHideSender(int $hideSender)
 * @method $this withInsurance(int $insurance)
 * @method $this withLabelDescription(int|string $labelDescription)
 * @method $this withLargeFormat(int $largeFormat)
 * @method $this withOnlyRecipient(int $onlyRecipient)
 * @method $this withReturn(int $return)
 * @method $this withSameDayDelivery(int $sameDayDelivery)
 * @method $this withSignature(int $signature)
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
            ->withAgeCheck(TriStateService::ENABLED)
            ->withHideSender(TriStateService::ENABLED)
            ->withInsurance(100)
            ->withLabelDescription('test')
            ->withLargeFormat(TriStateService::ENABLED)
            ->withOnlyRecipient(TriStateService::ENABLED)
            ->withReturn(TriStateService::ENABLED)
            ->withSameDayDelivery(TriStateService::ENABLED)
            ->withSignature(TriStateService::ENABLED);
    }
}
