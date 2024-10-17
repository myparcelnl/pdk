<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\PostNl;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * Morning and evening delivery are only available in the Netherlands.
 * When morning or evening delivery is enabled, signature and only recipient are required, but age check is not allowed.
 */
final class PostNLDeliveryTypeCalculator extends AbstractPdkOrderOptionCalculator
{
    private $postNLPickupCountries = [
        CountryCodes::CC_NL,
        CountryCodes::CC_BE,
        CountryCodes::CC_DE,
        CountryCodes::CC_DK,
        CountryCodes::CC_SE,
    ];

    public function calculate(): void
    {
        $deliveryOptions = $this->order->deliveryOptions;
        $cc              = $this->order->shippingAddress->cc;

        switch ($deliveryOptions->deliveryType) {
            case DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME:
                if (! in_array($cc, $this->postNLPickupCountries, true)) {
                    $deliveryOptions->deliveryType = DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME;

                    return;
                }
                $deliveryOptions->shipmentOptions->onlyRecipient = TriStateService::DISABLED;
                $deliveryOptions->shipmentOptions->return        = TriStateService::DISABLED;
                $deliveryOptions->shipmentOptions->signature     = TriStateService::DISABLED;

                if (CountryCodes::CC_NL === $cc) {
                    $deliveryOptions->shipmentOptions->signature = TriStateService::ENABLED;
                }
                break;
            // Todo: testcase dat alleen nederland hier komt.
            case DeliveryOptions::DELIVERY_TYPE_MORNING_NAME:
            case DeliveryOptions::DELIVERY_TYPE_EVENING_NAME:
                if (CountryCodes::CC_NL !== $cc) {
                    $deliveryOptions->deliveryType = DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME;

                    return;
                }

                $deliveryOptions->shipmentOptions->ageCheck      = TriStateService::DISABLED;
                $deliveryOptions->shipmentOptions->onlyRecipient = TriStateService::ENABLED;
                $deliveryOptions->shipmentOptions->signature     = TriStateService::ENABLED;
                break;
        }
    }
}
