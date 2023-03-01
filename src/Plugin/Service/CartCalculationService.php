<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

class CartCalculationService implements CartCalculationServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return array
     */
    public function calculateAllowedPackageTypes(PdkCart $cart): array
    {
        return (new Collection(DeliveryOptions::PACKAGE_TYPES_NAMES))
            ->filter(function ($packageType) use ($cart) {
                $lineHasPackageType = $cart->lines->containsStrict('product.settings.packageType', $packageType);

                if ($lineHasPackageType && DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME === $packageType) {
                    return $this->calculateMailboxPercentage($cart) <= 100.0;
                }

                return DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME === $packageType || $lineHasPackageType;
            })
            ->all();
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return mixed
     */
    public function calculateMailboxPercentage(PdkCart $cart)
    {
        if (! $cart->lines->every('product.settings.fitInMailbox', '>', 0)) {
            return INF;
        }

        return $cart->lines->reduce(static function ($carry, $line) {
            return $carry + $line->quantity * (100.0 / ($line->product->settings->fitInMailbox ?: 1));
        }, 0.0);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkShippingMethod
     */
    public function calculateShippingMethod(PdkCart $cart): PdkShippingMethod
    {
        $hasDeliveryOptions = $this->hasDeliveryOptions($cart);

        $shippingMethod = new PdkShippingMethod(['hasDeliveryOptions' => $hasDeliveryOptions]);

        if ($hasDeliveryOptions) {
            $shippingMethod->minimumDropOffDelay = $cart->lines->max('product.settings.dropOffDelay');
            $shippingMethod->allowPackageTypes   = $this->calculateAllowedPackageTypes($cart);
        }

        return $shippingMethod;
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return bool
     */
    protected function hasDeliveryOptions(PdkCart $cart): bool
    {
        $deliveryOptionsDisabled = $cart->lines->containsStrict('product.settings.disableDeliveryOptions', true);
        $anyItemIsDeliverable    = $cart->lines->containsStrict('product.isDeliverable', true);

        return $anyItemIsDeliverable && ! $deliveryOptionsDisabled;
    }
}
