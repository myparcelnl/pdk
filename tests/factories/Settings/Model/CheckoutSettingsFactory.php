<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @template T of CheckoutSettings
 * @method CheckoutSettings make()
 * @method $this withAllowedShippingMethods(Collection $allowedShippingMethods)
 * @method $this withDeliveryOptionsCustomCss(string $deliveryOptionsCustomCss)
 * @method $this withDeliveryOptionsHeader(string $deliveryOptionsHeader)
 * @method $this withDeliveryOptionsPosition(string $deliveryOptionsPosition)
 * @method $this withPickupLocationsDefaultView(string $pickupLocationsDefaultView)
 * @method $this withAllowPickupLocationsViewSelection(bool $allowPickupLocationsViewSelection)
 * @method $this withPriceType(string $priceType)
 * @method $this withShowDeliveryDay(bool $showDeliveryDay)
 * @method $this withUseSeparateAddressFields(bool $useSeparateAddressFields)
 */
final class CheckoutSettingsFactory extends AbstractSettingsModelFactory
{
    public function getModel(): string
    {
        return CheckoutSettings::class;
    }
}
