<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Service;

use MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Collection\PackageTypeCollection;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\PackageType;

class CartCalculationService implements CartCalculationServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\PackageTypeCollection
     */
    public function calculateAllowedPackageTypes(PdkCart $cart): PackageTypeCollection
    {
        return PackageTypeCollection::fromAll()
            ->sortBySize(true)
            ->filter(function (PackageType $packageType) use ($cart) {
                $packageTypeName    = $packageType->name;
                $lineHasPackageType = $cart->lines->containsStrict('product.mergedSettings.packageType', $packageTypeName);

                if ($this->isWeightUnderPackageTypeLimit($cart, $packageTypeName)) {
                    return false;
                }

                if ($lineHasPackageType && DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME === $packageTypeName) {
                    return $this->calculateMailboxPercentage($cart) <= 100.0;
                }

                return DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME === $packageTypeName || $lineHasPackageType;
            });
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return float
     */
    public function calculateMailboxPercentage(PdkCart $cart): float
    {
        if (! $cart->lines->every('product.settings.fitInMailbox', '>', 0)) {
            return INF;
        }

        return $cart->lines->reduce(static function ($carry, $line) {
            return $carry + $line->quantity * (100.0 / ($line->product->settings->fitInMailbox ?: 1));
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

        $shippingMethod = new PdkShippingMethod(['hasDeliveryOptions' => $hasDeliveryOptions]);

        if ($hasDeliveryOptions) {
            $shippingMethod->minimumDropOffDelay = $cart->lines->max('product.settings.dropOffDelay');
            $shippingMethod->allowedPackageTypes = $this->calculateAllowedPackageTypes($cart);
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
        $deliveryOptionsDisabled = $cart->lines->containsStrict('product.settings.disableDeliveryOptions', true);
        $anyItemIsDeliverable    = $cart->lines->isDeliverable();

        return $anyItemIsDeliverable && ! $deliveryOptionsDisabled;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     * @param  string                                 $packageType
     *
     * @return bool
     */
    private function isWeightUnderPackageTypeLimit(PdkCart $cart, string $packageType): bool
    {
        return $cart->lines->getTotalWeight() <= Arr::get(
                Pdk::get('packageTypeWeightLimits'),
                $packageType,
                20000
            );
    }
}
