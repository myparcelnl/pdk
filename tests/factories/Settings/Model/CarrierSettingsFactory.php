<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of CarrierSettings
 * @method $this withAllowDeliveryOptions(bool $allowDeliveryOptions)
 * @method $this withAllowEveningDelivery(bool $allowEveningDelivery)
 * @method $this withAllowMondayDelivery(bool $allowMondayDelivery)
 * @method $this withAllowMorningDelivery(bool $allowMorningDelivery)
 * @method $this withAllowOnlyRecipient(bool $allowOnlyRecipient)
 * @method $this withAllowPickupLocations(bool $allowPickupLocations)
 * @method $this withAllowSameDayDelivery(bool $allowSameDayDelivery)
 * @method $this withAllowSaturdayDelivery(bool $allowSaturdayDelivery)
 * @method $this withAllowSignature(bool $allowSignature)
 * @method $this withCutoffTime(string $cutoffTime)
 * @method $this withCutoffTimeSameDay(string $cutoffTimeSameDay)
 * @method $this withDefaultPackageType(string $defaultPackageType)
 * @method $this withDeliveryDaysWindow(int $deliveryDaysWindow)
 * @method $this withDeliveryOptionsCustomCss(string $deliveryOptionsCustomCss)
 * @method $this withDeliveryOptionsEnabled(bool $deliveryOptionsEnabled)
 * @method $this withDeliveryOptionsEnabledForBackorders(bool $deliveryOptionsEnabledForBackorders)
 * @method $this withDigitalStampDefaultWeight(int $digitalStampDefaultWeight)
 * @method $this withDropOffDelay(int $dropOffDelay)
 * @method $this withDropOffPossibilities(array|DropOffPossibilities|DropOffPossibilitiesFactory $dropOffPossibilities)
 * @method $this withExportAgeCheck(bool $exportAgeCheck)
 * @method $this withExportHideSender(bool $exportHideSender)
 * @method $this withExportInsurance(bool $exportInsurance)
 * @method $this withExportInsuranceFromAmount(int $exportInsuranceFromAmount)
 * @method $this withExportInsurancePriceFactor(float $exportInsurancePriceFactor)
 * @method $this withExportInsuranceUpTo(int $exportInsuranceUpTo)
 * @method $this withExportInsuranceUpToEu(int $exportInsuranceUpToEu)
 * @method $this withExportInsuranceUpToRow(int $exportInsuranceUpToRow)
 * @method $this withExportInsuranceUpToUnique(int $exportInsuranceUpToUnique)
 * @method $this withExportLargeFormat(bool $exportLargeFormat)
 * @method $this withExportOnlyRecipient(bool $exportOnlyRecipient)
 * @method $this withExportReturn(bool $exportReturn)
 * @method $this withExportReturnLargeFormat(bool $exportReturnLargeFormat)
 * @method $this withExportReturnPackageType(string $exportReturnPackageType)
 * @method $this withExportSignature(bool $exportSignature)
 * @method $this withPriceDeliveryTypeEvening(float $priceDeliveryTypeEvening)
 * @method $this withPriceDeliveryTypeMonday(float $priceDeliveryTypeMonday)
 * @method $this withPriceDeliveryTypeMorning(float $priceDeliveryTypeMorning)
 * @method $this withPriceDeliveryTypePickup(float $priceDeliveryTypePickup)
 * @method $this withPriceDeliveryTypeSameDay(float $priceDeliveryTypeSameDay)
 * @method $this withPriceDeliveryTypeSaturday(float $priceDeliveryTypeSaturday)
 * @method $this withPriceDeliveryTypeStandard(float $priceDeliveryTypeStandard)
 * @method $this withPriceOnlyRecipient(float $priceOnlyRecipient)
 * @method $this withPricePackageTypeDigitalStamp(float $pricePackageTypeDigitalStamp)
 * @method $this withPricePackageTypeMailbox(float $pricePackageTypeMailbox)
 * @method $this withPriceSignature(float $priceSignature)
 * @method $this withAllowInternationalMailbox(bool $allowInternationalMailbox)
 * @method $this withPriceInternationalMailbox(float $priceInternationalMailbox)
 */
final class CarrierSettingsFactory extends AbstractSettingsModelFactory
{
    /**
     * @param  null|string $id
     */
    public function __construct(string $id = null)
    {
        parent::__construct();

        if ($id) {
            $this->withId($id);
        }
    }

    public function getModel(): string
    {
        return CarrierSettings::class;
    }

    /**
     * @param  string $id
     *
     * @return $this
     */
    public function withId(string $id): self
    {
        return $this->with(['id' => $id]);
    }

    protected function save(Model $model): void
    {
        factory(Settings::class)
            ->withCarrier($this->attributes['id'], $model)
            ->store();
    }
}
