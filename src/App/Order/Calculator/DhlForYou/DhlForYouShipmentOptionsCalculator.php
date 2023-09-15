<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\DhlForYou;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * - Only recipient is only available for NL
 * - Age check is only available for NL
 * - Age check and only recipient are mutually exclusive
 */
final class DhlForYouShipmentOptionsCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var \MyParcelNL\Pdk\Shipment\Model\ShipmentOptions
     */
    private $shipmentOptions;

    public function calculate(): void
    {
        $this->shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        $this->calculateOptionsForCountry();
        $this->calculateAgeCheckAndOnlyRecipient();
    }

    private function calculateAgeCheckAndOnlyRecipient(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        if (TriStateService::ENABLED === $shipmentOptions->ageCheck) {
            $shipmentOptions->onlyRecipient = TriStateService::DISABLED;
        }

        if (TriStateService::ENABLED === $shipmentOptions->onlyRecipient) {
            $shipmentOptions->ageCheck = TriStateService::DISABLED;
        }
    }

    private function calculateOptionsForCountry(): void
    {
        if (CountryCodes::CC_NL === $this->order->shippingAddress->cc) {
            return;
        }

        $this->shipmentOptions->ageCheck      = TriStateService::DISABLED;
        $this->shipmentOptions->onlyRecipient = TriStateService::DISABLED;
    }
}
