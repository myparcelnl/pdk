<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Validation\Repository\SchemaRepository;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class InsuranceCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface
     */
    private $countryService;

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface
     */
    private $currencyService;

    /**
     * @var \MyParcelNL\Pdk\Validation\Repository\SchemaRepository
     */
    private $schemaRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);

        $this->countryService  = Pdk::get(CountryServiceInterface::class);
        $this->currencyService = Pdk::get(CurrencyServiceInterface::class);
        $this->schemaRepository = Pdk::get(SchemaRepository::class);

    }

    /**
     * @return void
     */
    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        $shipmentOptions->insurance = $this->calculateInsurance($shipmentOptions->insurance);
    }

    /**
     * @param  null|int $amount
     *
     * @return int
     */
    private function calculateInsurance(?int $amount): int
    {
        if (null === $amount || TriStateService::DISABLED === $amount) {
            /** @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $schema */
            $schema        = Pdk::get(CarrierSchema::class);
            $carrierSchema = $schema->setCarrier($this->order->deliveryOptions->carrier);

            return $carrierSchema->getAllowedInsuranceAmounts()[0] ?? 0;
        }

        $carrierSettings   = CarrierSettings::fromCarrier($this->order->deliveryOptions->carrier);
        $enabledViaCarrier = TriStateService::INHERIT === $amount && $carrierSettings->exportInsurance;

        if ($amount > TriStateService::ENABLED && ! $enabledViaCarrier) {
            return $this->getMaxInsurance($carrierSettings, $amount);
        }

        $orderAmount = (int) ceil(
            $carrierSettings->exportInsurancePricePercentage * $this->order->orderPriceAfterVat / 100
        );

        $fromAmount = $this->currencyService->convertToCents($carrierSettings->exportInsuranceFromAmount);

        if ($orderAmount < $fromAmount) {
            return 0;
        }

        return $this->getMaxInsurance($carrierSettings, $orderAmount);
    }

    /**
     * @param  null|string $cc
     *
     * @return string
     * @noinspection MultipleReturnStatementsInspection
     */
    private function getInsuranceUpToKey(?string $cc): string
    {
        $country = $cc ?? Platform::get('localCountry');

        if ($this->countryService->isLocalCountry($country)) {
            return CarrierSettings::EXPORT_INSURANCE_UP_TO;
        }

        if ($this->countryService->isUnique($country)) {
            return CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE;
        }

        if ($this->countryService->isEu($country)) {
            return CarrierSettings::EXPORT_INSURANCE_UP_TO_EU;
        }

        return CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW;
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings $carrierSettings
     * @param  int                                            $amount
     *
     * @return mixed
     */
    private function getMaxInsurance(CarrierSettings $carrierSettings, int $amount)
    {
        // Get a schema resolved to this specific order's combination of carrier, country, package type and delivery type.
        $orderSchema = $this->schemaRepository->getOrderValidationSchema(
            $this->order->deliveryOptions->carrier->name ?? Platform::get('defaultCarrier'),
            $this->order->shippingAddress->cc ?? null,
            $this->order->deliveryOptions->packageType,
            $this->order->deliveryOptions->deliveryType
        );

        // The key below is terrible, and is a placeholder until we can get this from the Capabilities API.
        $allowedInsuranceAmounts = $this->schemaRepository->getValidOptions(
            $orderSchema,
            'properties.deliveryOptions.properties.shipmentOptions.properties.' . ShipmentOptions::INSURANCE
        );

        $insuranceUpToKey  = $this->getInsuranceUpToKey($this->order->shippingAddress->cc);
        $maxInsuranceValue = $carrierSettings->getAttribute($insuranceUpToKey) ?? 0;

        return min(
            $this->getMinimumInsuranceAmount($allowedInsuranceAmounts, $amount),
            $maxInsuranceValue
        );
    }

    /**
     * @param  int[] $insuranceAmount
     * @param  int   $orderAmount
     *
     * @return int
     */
    private function getMinimumInsuranceAmount(array $insuranceAmount, int $orderAmount): int
    {
        foreach ($insuranceAmount as $allowedInsuranceAmount) {
            if ($allowedInsuranceAmount < $orderAmount) {
                continue;
            }

            return $allowedInsuranceAmount;
        }

        return $orderAmount;
    }
}
