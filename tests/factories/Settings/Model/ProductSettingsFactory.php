<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;

/**
 * @template T of ProductSettings
 * @method ProductSettings make()
 * @method $this withCountryOfOrigin(string $countryOfOrigin)
 * @method $this withCustomsCode(string $customsCode)
 * @method $this withDisableDeliveryOptions(bool $disableDeliveryOptions)
 * @method $this withDropOffDelay(int $dropOffDelay)
 * @method $this withExportAgeCheck(int $exportAgeCheck)
 * @method $this withExportHideSender(int $exportHideSender)
 * @method $this withExportInsurance(int $exportInsurance)
 * @method $this withExportLargeFormat(int $exportLargeFormat)
 * @method $this withExportOnlyRecipient(int $exportOnlyRecipient)
 * @method $this withExportReturn(int $exportReturn)
 * @method $this withExportSignature(int $exportSignature)
 * @method $this withFitInMailbox(int $fitInMailbox)
 * @method $this withPackageType(string $packageType)
 */
final class ProductSettingsFactory extends AbstractSettingsModelFactory
{
    public function getModel(): string
    {
        return ProductSettings::class;
    }

    /**
     * @return $this
     */
    public function withAllOptions(): self
    {
        return $this
            ->withCountryOfOrigin(Platform::get('localCountry'))
            ->withCustomsCode('123456')
            ->withDisableDeliveryOptions(true)
            ->withDropOffDelay(3)
            ->withExportAgeCheck(TriStateService::ENABLED)
            ->withExportHideSender(TriStateService::ENABLED)
            ->withExportInsurance(TriStateService::ENABLED)
            ->withExportLargeFormat(TriStateService::ENABLED)
            ->withExportOnlyRecipient(TriStateService::ENABLED)
            ->withExportReturn(TriStateService::ENABLED)
            ->withExportSignature(TriStateService::ENABLED)
            ->withFitInMailbox(0);
    }

    /**
     * @return $this
     */
    public function withMailboxOptions(): self
    {
        return $this
            ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
            ->withFitInMailbox(3);
    }

    /**
     * @return $this
     */
    protected function createDefault(): FactoryInterface
    {
        return $this->withCountryOfOrigin(Platform::get('localCountry'));
    }
}
