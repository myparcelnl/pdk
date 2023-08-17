<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

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
 * @method $this withExportSignature(int $exportSignature)
 * @method $this withFitInDigitalStamp(int $fitInDigitalStamp)
 * @method $this withFitInMailbox(int $fitInMailbox)
 * @method $this withPackageType(string $packageType)
 * @method $this withReturnShipments(int $returnShipments)
 */
final class ProductSettingsFactory extends AbstractSettingsModelFactory
{
    public function getModel(): string
    {
        return ProductSettings::class;
    }
}
