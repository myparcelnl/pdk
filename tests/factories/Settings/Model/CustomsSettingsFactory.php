<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @template T of CustomsSettings
 * @method CustomsSettings make()
 * @method $this withCountryOfOrigin(string $countryOfOrigin)
 * @method $this withCustomsCode(string $customsCode)
 * @method $this withPackageContents(int $packageContents)
 */
final class CustomsSettingsFactory extends AbstractSettingsModelFactory
{
    public function getModel(): string
    {
        return CustomsSettings::class;
    }
}
