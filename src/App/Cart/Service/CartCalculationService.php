<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Service;

use MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\PackageType;
use MyParcelNL\Pdk\Types\Service\TriStateService;

class CartCalculationService implements CartCalculationServiceInterface
{

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return float
     */
    public function calculateMailboxPercentage(PdkCart $cart): float
    {
        if ($cart->lines->where('product.mergedSettings.fitInMailbox', 0)
                ->count() > 0) {
            return INF;
        }

        return $cart->lines->reduce(static function ($carry, $line) {
            $fitInMailbox = $line->product->mergedSettings->fitInMailbox;

            return $fitInMailbox === TriStateService::INHERIT
                ? $carry
                : $carry + $line->quantity * (100.0 / $fitInMailbox);
        }, 0.0);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return \MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod
     */
    public function calculateShippingMethod(PdkCart $cart): PdkShippingMethod
    {
        $hasDeliveryOptions = $this->hasDeliveryOptions($cart);

        $shippingMethod                     = $cart->shippingMethod;
        $shippingMethod->hasDeliveryOptions = $hasDeliveryOptions;
        $shippingMethod->excludeParcelLockers = $this->shouldExcludeParcelLockers($cart);

        if ($hasDeliveryOptions) {
            $shippingMethod->minimumDropOffDelay = $cart->lines->max('product.settings.dropOffDelay');
        }

        return $shippingMethod;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return bool
     */
    protected function hasDeliveryOptions(PdkCart $cart): bool
    {
        $deliveryOptionsDisabled = $cart->lines->containsStrict(
            'product.mergedSettings.disableDeliveryOptions',
            TriStateService::ENABLED
        );
        $anyItemIsDeliverable    = $cart->lines->isDeliverable();

        return $anyItemIsDeliverable && ! $deliveryOptionsDisabled;
    }

    /**
     * Check if parcel lockers should be excluded for this cart.
     *
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return bool
     */
    protected function shouldExcludeParcelLockers(PdkCart $cart): bool
    {
        // Check if parcel lockers should be excluded based on general setting
        $generalExcludeParcelLockers = Settings::get(
            CheckoutSettings::EXCLUDE_PARCEL_LOCKERS,
            CheckoutSettings::ID,
            false
        );

        // Check if any product in the cart has 18+ classification (product level)
        $has18PlusProduct = $cart->lines->contains(function ($line) {
            if (! is_object($line) || ! $line->product || ! $line->product->mergedSettings) {
                return false;
            }
            $productSettings = $line->product->mergedSettings;
            return TriStateService::ENABLED === $productSettings->exportAgeCheck;
        });

        // Check if any product explicitly excludes parcel lockers (product level)
        $productExcludesParcelLockers = $cart->lines->contains(function ($line) {
            if (! is_object($line) || ! $line->product || ! $line->product->mergedSettings) {
                return false;
            }
            $productSettings = $line->product->mergedSettings;
            return TriStateService::ENABLED === $productSettings->excludeParcelLockers;
        });

        // Check if 18+ is enabled on carrier level for any carrier in the cart
        $has18PlusCarrier = $cart->lines->contains(function ($line) {
            if (! is_object($line) || ! $line->product || ! $line->product->carrier || ! $line->product->carrier->id) {
                return false;
            }
            
            return Settings::get(
                CarrierSettings::EXPORT_AGE_CHECK,
                CarrierSettings::ID . '.' . $line->product->carrier->id,
                false
            );
        });

        return $generalExcludeParcelLockers || $has18PlusProduct || $productExcludesParcelLockers || $has18PlusCarrier;
    }

    /**
     * Get the unique package types requested by products in the cart.
     *
     * Resolves INHERIT to the default package type. Returns deduplicated values.
     *
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return string[] PDK package type names
     */
    public function getCartPackageTypes(PdkCart $cart): array
    {
        return $cart->lines
            ->pluck('product.settings.packageType')
            ->map(static function ($packageType) {
                if ((new TriStateService())->cast($packageType) === TriStateService::INHERIT) {
                    return DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME;
                }

                return $packageType;
            })
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Calculate the total cart weight including empty package weight for the given package type.
     *
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     * @param  string                                  $packageTypeName
     *
     * @return int
     */
    public function getCartWeightForPackageType(PdkCart $cart, string $packageTypeName): int
    {
        return Pdk::get(WeightServiceInterface::class)
            ->addEmptyPackageWeight($cart->lines->getTotalWeight(), new PackageType([
                'name' => $packageTypeName,
                'id'   => DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP[$packageTypeName] ?? null,
            ]));
    }
}
