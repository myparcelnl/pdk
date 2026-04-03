<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Validation\Repository\SchemaRepository;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class InsuranceCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * Schema path used to resolve carrier- and context-specific insurance tier deviations from the base JSON schemas.
     * The JSON schemas act as an override for specific carrier/country/packageType/deliveryType combinations.
     *
     * @TODO INT-930: replace schema-based tier deviations with capabilities API tier lists when the API supports
     *               explicit per-carrier tier definitions.
     * @var string
     */
    public const INSURANCE_SCHEMA_PREFIX = 'properties.deliveryOptions.properties.shipmentOptions.properties.insurance';

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

        $this->countryService   = Pdk::get(CountryServiceInterface::class);
        $this->currencyService  = Pdk::get(CurrencyServiceInterface::class);
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
     * Given the insurance amount, calculate the final insurance value.
     *
     * The result always falls within the carrier's [min, max] insurance range:
     * - NULL or DISABLED (0): use carrier minimum.
     * - INHERIT (-1): fall back to settings; if settings do not enable insurance, use carrier default.
     * - Explicit amount: resolve to nearest valid tier.
     *
     * @param  null|int $amount
     *
     * @return int
     */
    private function calculateInsurance(?int $amount): int
    {
        $carrier          = $this->order->deliveryOptions->carrier;
        $carrierInsurance = $carrier->options ? $carrier->options->getInsurance() : null;

        // No insurance possible? Return 0
        if (null === $carrierInsurance) {
            return 0;
        }

        $insuredAmount  = $carrierInsurance->getInsuredAmount();
        $carrierMin     = $insuredAmount->getMin()->getAmount();
        $carrierMax     = $insuredAmount->getMax()->getAmount();
        $carrierDefault = $insuredAmount->getDefault()->getAmount();

        // No insurance set? We still need to respect the carrier's minimum insurance amount or the request will fail.
        if (null === $amount || TriStateService::DISABLED === $amount) {
            return $carrierMin;
        }

        if (TriStateService::INHERIT === $amount) {
            return $this->calculateFromSettings($carrier, $carrierMin, $carrierMax, $carrierDefault);
        }

        // Explicit amount: resolve to nearest valid tier, clamp to carrier range.
        $allowedAmounts = $this->resolveAllowedInsuranceAmounts($carrier);
        $validated      = $this->getMinimumInsuranceAmount($allowedAmounts, $amount);

        return $this->clampToCarrierRange($validated, $carrierMin, $carrierMax);
    }

    /**
     * Calculate insurance from carrier settings when the shipment option is set to INHERIT.
     *
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     * @param  int                                    $carrierMin
     * @param  int                                    $carrierMax
     * @param  int                                    $carrierDefault
     *
     * @return int
     */
    private function calculateFromSettings(Carrier $carrier, int $carrierMin, int $carrierMax, int $carrierDefault): int
    {
        $carrierSettings = CarrierSettings::fromCarrier($carrier);

        if (! $carrierSettings->exportInsurance) {
            return $this->clampToCarrierRange($carrierDefault, $carrierMin, $carrierMax);
        }

        $orderAmount = (int) ceil(
            $carrierSettings->exportInsurancePricePercentage * $this->order->orderPriceAfterVat / 100
        );

        $fromAmount = $this->currencyService->convertToCents($carrierSettings->exportInsuranceFromAmount);

        if ($orderAmount < $fromAmount) {
            // The minimum insurance from settings applies as "insure orders from".
            // If the request is therefore not to insure here, set it to the minimum of the carrier (usually 0)
            return $carrierMin;
        }

        $allowedAmounts = $this->resolveAllowedInsuranceAmounts($carrier);
        $validated      = $this->getMinimumInsuranceAmount($allowedAmounts, $orderAmount);

        $insuranceUpToKey  = $this->getInsuranceUpToKey($this->order->shippingAddress->cc);
        $maxInsuranceValue = $carrierSettings->getAttribute($insuranceUpToKey) ?? 0;
        $settingsResult    = min($validated, $maxInsuranceValue);

        return $this->clampToCarrierRange($settingsResult, $carrierMin, $carrierMax);
    }

    /**
     * Clamp the given amount to the carrier's allowed insurance range.
     *
     * @param  int $amount
     * @param  int $min
     * @param  int $max
     *
     * @return int
     */
    private function clampToCarrierRange(int $amount, int $min, int $max): int
    {
        return max($min, min($amount, $max));
    }

    /**
     * @param  null|string $cc
     *
     * @return string
     * @noinspection MultipleReturnStatementsInspection
     */
    private function getInsuranceUpToKey(?string $cc): string
    {
        if ($cc) {
            $country = $cc;
        } else {
            $country = Pdk::get(PropositionService::class)->getPropositionConfig()->countryCode;
        }

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
     * Resolve the list of allowed insurance tier amounts for the given carrier and order context.
     *
     * The JSON schema is consulted first: if it defines an explicit enum of tiers for this
     * carrier/country/packageType/deliveryType combination, those tiers are used as-is.
     * This allows platform-specific deviations (e.g. PostNL NL has finer low-end steps).
     *
     * When no schema enum is defined the carrier capabilities range is used as the fallback,
     * producing tiers from min to max in 50 000-cent steps.
     *
     * @TODO INT-930: once the capabilities API supports explicit per-carrier tier lists, remove the
     *               schema lookup entirely and always derive tiers from capabilities.
     *
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return int[]
     */
    private function resolveAllowedInsuranceAmounts(Carrier $carrier): array
    {
        $carrierName = $carrier->carrier ?? Pdk::get(PropositionService::class)->getDefaultCarrier()->carrier;

        // @TODO INT-930: replace schema-based tier deviations with capabilities API tier lists.
        $orderSchema = $this->schemaRepository->getOrderValidationSchema(
            $carrierName,
            $this->order->shippingAddress->cc ?? null,
            $this->order->deliveryOptions->packageType,
            $this->order->deliveryOptions->deliveryType
        );

        $schemaTiers = $this->schemaRepository->getValidOptions($orderSchema, self::INSURANCE_SCHEMA_PREFIX);

        if (! empty($schemaTiers)) {
            return $schemaTiers;
        }

        // No schema-defined tiers for this context: fall back to capabilities.
        $carrierSchema = Pdk::get(CarrierSchema::class)->setCarrier($carrier);

        return $carrierSchema->getAllowedInsuranceAmounts();
    }

    /**
     * Return the lowest tier that is >= $orderAmount.
     * When $orderAmount exceeds every tier the highest tier is returned, capping the amount
     * at the carrier's capabilities maximum rather than allowing an unbounded value.
     *
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

        // $orderAmount exceeds all tiers: cap at the highest (= capabilities max).
        return end($insuranceAmount) ?: $orderAmount;
    }
}
