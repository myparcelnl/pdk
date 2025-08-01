<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Gls;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Base\Service\CountryCodes;

/**
 * GLS business rules:
 * - Netherlands: signature is default OFF
 * - International (EU): signature is always ON (mandatory)
 * - When signature is ON: insurance is automatically ON (coupled)
 */
final class GlsShipmentOptionsCalculator extends AbstractPdkOrderOptionCalculator
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
        $this->calculateSignatureForCountry();
        $this->calculateInsuranceBasedOnSignature();
    }

    /**
     * Netherlands: signature default OFF
     * International (EU): signature always ON (mandatory)
     */
    private function calculateSignatureForCountry(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;
        $countryCode = $this->order->shippingAddress->cc;


        if (CountryCodes::CC_NL === $countryCode || null === $countryCode) {
            return; // signature default OFF in local country (NL)
        }

        // Enable signature for EU countries and Belgium specifically
        // (Belgium is in UNIQUE_COUNTRIES so isEu() returns false, but for shipping logic it's EU)
        if ($this->countryService->isEu($countryCode) || CountryCodes::CC_BE === $countryCode) {
            $shipmentOptions->signature = TriStateService::ENABLED;
        }
    }

    /**
     * When signature is ON: insurance is automatically ON (coupled)
     */
    private function calculateInsuranceBasedOnSignature(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        if (TriStateService::ENABLED === $shipmentOptions->signature) {
            $shipmentOptions->insurance = TriStateService::ENABLED;
        }
    }
}
