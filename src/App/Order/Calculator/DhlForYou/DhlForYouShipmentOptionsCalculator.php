<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\DhlForYou;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * - Only recipient is only available for NL
 * - Age check is only available for NL
 * - Age check and only recipient are mutually exclusive
 */
final class DhlForYouShipmentOptionsCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface
     */
    private $countryService;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);
        $this->countryService = Pdk::get(CountryServiceInterface::class);
    }

    public function calculate(): void
    {
        $this->calculateOptionsForCountry();
        $this->calculateAgeCheckAndOnlyRecipient();
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    private function calculateOptionsForCountry(): void
    {
        if ($this->countryService->isLocalCountry($this->order->shippingAddress->cc)) {
            return;
        }

        $this->order->deliveryOptions->shipmentOptions->ageCheck      = TriStateService::DISABLED;
        $this->order->deliveryOptions->shipmentOptions->onlyRecipient = TriStateService::DISABLED;
    }
}
