<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of DeliveryOptionsConfig
 * @method DeliveryOptionsConfig make()
 * @method $this withAllowRetry(bool $allowRetry)
 * @method $this withApiBaseUrl(string $apiBaseUrl)
 * @method $this withBasePrice(int $basePrice)
 * @method $this withCarrierSettings(array $carrierSettings)
 * @method $this withCurrency(string $currency)
 * @method $this withLocale(string $locale)
 * @method $this withPackageType(string $packageType)
 * @method $this withPickupLocationsDefaultView(string $pickupLocationsDefaultView)
 * @method $this withPlatform(string $platform)
 * @method $this withPriceDeliveryTypeTypeStandard(int $priceDeliveryTypeTypeStandard)
 * @method $this withShowPriceSurcharge(bool $showPriceSurcharge)
 */
final class DeliveryOptionsConfigFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return DeliveryOptionsConfig::class;
    }
}
