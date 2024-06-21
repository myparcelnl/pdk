<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Service;

use MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Base\Service\WeightService;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Collection\PackageTypeCollection;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\PackageType;
use MyParcelNL\Pdk\Types\Service\TriStateService;

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
                $packageTypeName = $packageType->name;

                if (DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME === $packageTypeName) {
                    return true;
                }

                $allowed = $cart->lines->containsStrict('product.mergedSettings.packageType', $packageTypeName)
                    && $this->isWeightUnderPackageTypeLimit($cart, $packageType);

                if (DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME === $packageTypeName) {
                    return $allowed && $this->calculateMailboxPercentage($cart) <= 100.0;
                }

                return $allowed;
            });
    }

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
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart     $cart
     * @param  \MyParcelNL\Pdk\Shipment\Model\PackageType $packageType
     *
     * @return bool
     */
    private function isWeightUnderPackageTypeLimit(PdkCart $cart, PackageType $packageType): bool
    {
        $limit  = Arr::get(Pdk::get('packageTypeWeightLimits'), $packageType->name, INF);
        $weight = Pdk::get(WeightService::class)
            ->addEmptyPackageWeight($cart->lines->getTotalWeight(), $packageType);

        return $weight <= $limit;
    }
}
