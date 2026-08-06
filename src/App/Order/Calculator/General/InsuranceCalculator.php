<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Carrier\Util\InsuranceTierMath;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsInsuranceOptionV2;

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
     * @var \MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService
     */
    private $capabilitiesService;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);

        $this->countryService      = Pdk::get(CountryServiceInterface::class);
        $this->currencyService     = Pdk::get(CurrencyServiceInterface::class);
        $this->capabilitiesService = Pdk::get(CapabilitiesValidationService::class);
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
     * Bounds (min/max/default) come from the per-shipment capability for the
     * resolved carrier+country+package_type+delivery_type combination — these can
     * narrow below the carrier-wide contract range. Tier resolution and clamping
     * use those shipment-specific bounds.
     *
     * The capability advertises them as flat `min`/`max`/`default` money objects, each
     * optional. An absent minimum means the carrier imposes no floor, an absent maximum
     * means it imposes no ceiling, and an absent default falls back to the minimum.
     *
     * - NULL or DISABLED (0): use carrier minimum.
     * - INHERIT (-1) or ENABLED (1): fall back to settings; if settings do not enable insurance,
     *   use carrier default.
     * - Explicit amount (> 1): resolve to nearest valid tier.
     *
     * @param  null|int $amount
     *
     * @return int
     */
    private function calculateInsurance(?int $amount): int
    {
        $carrier          = $this->order->deliveryOptions->carrier;
        $carrierInsurance = $this->fetchShipmentInsurance($carrier);

        // No insurance possible for this shipment context.
        if (null === $carrierInsurance) {
            return 0;
        }

        $min     = $carrierInsurance->getMin();
        $max     = $carrierInsurance->getMax();
        $default = $carrierInsurance->getDefault();

        $carrierMin     = $min ? $min->getAmount() : 0;
        $carrierMax     = $max ? $max->getAmount() : null;
        $carrierDefault = $default ? $default->getAmount() : $carrierMin;

        // No insurance set? We still need to respect the carrier's minimum insurance amount or the request will fail.
        if (null === $amount || TriStateService::DISABLED === $amount) {
            return $carrierMin;
        }

        // ENABLED means "insurance is on, derive the amount from the carrier settings" — never a
        // literal insured amount of 1 cent. TriStateOptionCalculator resolves INHERIT to ENABLED
        // from the exportInsurance setting before this calculator runs, and
        // CapabilitiesOptionCalculator forces ENABLED for required options, so this is the value
        // real exports arrive with.
        if (TriStateService::INHERIT === $amount || TriStateService::ENABLED === $amount) {
            return $this->calculateFromSettings($carrier, $carrierMin, $carrierMax, $carrierDefault);
        }

        return $this->resolveToTier($amount, $carrierMin, $carrierMax);
    }

    /**
     * Resolve a requested amount to a value the carrier accepts.
     *
     * Snaps the amount up to the nearest tier in the carrier's range, then clamps it to that
     * range. Without a carrier maximum there is no ladder to snap to, so the amount passes
     * through with only the carrier minimum applied as a floor.
     *
     * @param  int      $amount
     * @param  int      $min
     * @param  null|int $max
     *
     * @return int
     */
    private function resolveToTier(int $amount, int $min, ?int $max): int
    {
        if (null === $max) {
            return max($min, $amount);
        }

        $allowedAmounts = InsuranceTierMath::buildTiers($min, $max);
        $validated      = $this->getMinimumInsuranceAmount($allowedAmounts, $amount);

        return $this->clampToCarrierRange($validated, $min, $max);
    }

    /**
     * Fetch the insurance option from the shipment-context capability.
     *
     * @return null|\MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsInsuranceOptionV2
     */
    private function fetchShipmentInsurance(Carrier $carrier): ?RefCapabilitiesResponseOptionsInsuranceOptionV2
    {
        $deliveryOptions = $this->order->deliveryOptions;
        $cc              = $this->order->shippingAddress->cc;
        $v2PackageType   = DeliveryOptions::PACKAGE_TYPES_V2_MAP[$deliveryOptions->packageType] ?? null;
        $v2DeliveryType  = DeliveryOptions::DELIVERY_TYPES_V2_MAP[$deliveryOptions->deliveryType] ?? null;

        if (! $v2PackageType) {
            return null;
        }

        $args = [
            'carrier'      => $carrier->carrier,
            'recipient'    => $this->capabilitiesRecipient(),
            'package_type' => $v2PackageType,
        ];

        if ($v2DeliveryType) {
            $args['delivery_type'] = $v2DeliveryType;
        }

        $capability = $this->capabilitiesService->getRepository()->getCapabilities($args)[0] ?? null;

        if (! $capability) {
            return null;
        }

        $options = $capability->getOptions();

        return $options ? $options->getInsurance() : null; // @phpstan-ignore-line SDK declares non-nullable but API may omit
    }

    /**
     * Calculate insurance from carrier settings when the shipment option is set to INHERIT.
     *
     * With insurance switched off in the settings the carrier default applies. Otherwise the
     * "insure up to" setting caps the calculated amount; it defaults to 0 when unset, which
     * brings the result down to the carrier minimum.
     *
     * Without a carrier maximum the amount is not snapped to a tier.
     *
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     * @param  int                                    $carrierMin
     * @param  null|int                               $carrierMax
     * @param  int                                    $carrierDefault
     *
     * @return int
     */
    private function calculateFromSettings(
        Carrier $carrier,
        int $carrierMin,
        ?int $carrierMax,
        int $carrierDefault
    ): int {
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

        $validated = $this->resolveToTier($orderAmount, $carrierMin, $carrierMax);

        $insuranceUpToKey  = $this->getInsuranceUpToKey($this->order->shippingAddress->cc);
        $maxInsuranceValue = $carrierSettings->getAttribute($insuranceUpToKey) ?? 0;
        $settingsResult    = min($validated, $maxInsuranceValue);

        return $this->clampToCarrierRange($settingsResult, $carrierMin, $carrierMax);
    }

    /**
     * Clamp the given amount to the carrier's allowed insurance range.
     *
     * A null maximum means the carrier advertises no ceiling, so only the floor applies.
     *
     * @param  int      $amount
     * @param  int      $min
     * @param  null|int $max
     *
     * @return int
     */
    private function clampToCarrierRange(int $amount, int $min, ?int $max): int
    {
        $floored = max($min, $amount);

        return null === $max ? $floored : min($floored, $max);
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
