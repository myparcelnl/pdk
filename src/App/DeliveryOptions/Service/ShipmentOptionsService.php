<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use Throwable;

class ShipmentOptionsService implements ShipmentOptionsServiceInterface
{
    private const CARRIER_SETTING_KEY = 'carrierSetting';
    private const PRODUCT_SETTING_KEY = 'productSetting';
    private const SETTING_KEYS        = [
        [
            self::SHIPMENT_OPTION_KEY => ShipmentOptions::AGE_CHECK,
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_AGE_CHECK,
            self::PRODUCT_SETTING_KEY => ProductSettings::EXPORT_AGE_CHECK,
        ],
        [
            self::SHIPMENT_OPTION_KEY => ShipmentOptions::INSURANCE,
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_INSURANCE,
            self::PRODUCT_SETTING_KEY => ProductSettings::EXPORT_INSURANCE,
        ],
        [
            self::SHIPMENT_OPTION_KEY => ShipmentOptions::LARGE_FORMAT,
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_LARGE_FORMAT,
            self::PRODUCT_SETTING_KEY => ProductSettings::EXPORT_LARGE_FORMAT,
        ],
        [
            self::SHIPMENT_OPTION_KEY => ShipmentOptions::ONLY_RECIPIENT,
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_ONLY_RECIPIENT,
            self::PRODUCT_SETTING_KEY => ProductSettings::EXPORT_ONLY_RECIPIENT,
        ],
        [
            self::SHIPMENT_OPTION_KEY => ShipmentOptions::RETURN,
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_RETURN,
            self::PRODUCT_SETTING_KEY => ProductSettings::EXPORT_RETURN,
        ],
        [
            self::SHIPMENT_OPTION_KEY => ShipmentOptions::SIGNATURE,
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_SIGNATURE,
            self::PRODUCT_SETTING_KEY => ProductSettings::EXPORT_SIGNATURE,
        ],
    ];
    private const SHIPMENT_OPTION_KEY = 'shipmentOption';

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface
     */
    private $countryService;

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface
     */
    private $currencyService;

    /**
     * @param  \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface $currencyService
     * @param  \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface  $countryService
     */
    public function __construct(CurrencyServiceInterface $currencyService, CountryServiceInterface $countryService)
    {
        $this->countryService  = $countryService;
        $this->currencyService = $currencyService;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return void
     */
    public function calculate(PdkOrder $order): void
    {
        try {
            $order->deliveryOptions->shipmentOptions = $this->mergeOrderLines($order);
        } catch (Throwable $e) {
            Logger::error('Could not calculate shipment options', ['exception' => $e]);
            return;
        }
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return mixed
     */
    public function mergeProductSettings(PdkOrder $order)
    {
        return $order->lines->reduce(function (ProductSettings $acc, PdkOrderLine $line) {
            foreach ($line->product->settings->getAttributes() as $attribute => $value) {
                $acc->setAttribute($attribute, $this->valueProcessor($acc->getAttribute($attribute), $value));
            }

            return $acc;
        }, new ProductSettings());
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return int
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function calculateInsurance(PdkOrder $order): int
    {
        $carrierSettings = CarrierSettings::fromCarrier($order->deliveryOptions->carrier);

        $orderAmount = (int) ceil($carrierSettings->exportInsurancePriceFactor * $order->orderPriceAfterVat);
        $fromAmount  = $this->currencyService->convertToCents($carrierSettings->exportInsuranceFromAmount);

        if ($orderAmount < $fromAmount) {
            return 0;
        }

        $allowedInsuranceAmounts = $order
            ->getValidator()
            ->getAllowedInsuranceAmounts();

        $insuranceUpToKey  = $this->getInsuranceUpToKey($order->shippingAddress->cc);
        $maxInsuranceValue = $this->currencyService->convertToCents($carrierSettings[$insuranceUpToKey] ?? 0);

        return min(
            $this->getMinimumInsuranceAmount($allowedInsuranceAmounts, $orderAmount),
            $maxInsuranceValue
        );
    }

    /**
     * @param  null|string $cc
     *
     * @return string
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
     * @param  int[] $insuranceAmount
     * @param  int   $orderAmount
     *
     * @return void
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

    /**
     * values in $shipmentOptions have priority over calculated values from $lines
     *
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\ShipmentOptions
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function mergeOrderLines(PdkOrder $order): ShipmentOptions
    {
        $carrierSettings = CarrierSettings::fromCarrier($order->deliveryOptions->carrier);
        $productSettings = $this->mergeProductSettings($order);
        $shipmentOptions = $order->deliveryOptions->shipmentOptions;

        foreach (self::SETTING_KEYS as $keys) {
            $shipmentOptionKey = $keys[self::SHIPMENT_OPTION_KEY];

            // If value is already set, keep it.
            if (null !== $shipmentOptions->getAttribute($shipmentOptionKey)) {
                continue;
            }

            $carrierSettingKey  = $keys[self::CARRIER_SETTING_KEY];
            $productSettingsKey = $keys[self::PRODUCT_SETTING_KEY];

            if (isset($productSettings[$productSettingsKey]) && AbstractSettingsModel::TRISTATE_VALUE_DEFAULT !== $productSettings[$productSettingsKey]) {
                $shipmentOptions->setAttribute($shipmentOptionKey, $productSettings[$productSettingsKey]);
            } else {
                $shipmentOptions->setAttribute(
                    $shipmentOptionKey,
                    $carrierSettings[$carrierSettingKey] ?? $productSettings[$productSettingsKey] ?? null
                );
            }
        }

        if (AbstractSettingsModel::TRISTATE_VALUE_ENABLED === $shipmentOptions->insurance) {
            $shipmentOptions->insurance = $this->calculateInsurance($order);
        }

        return $shipmentOptions;
    }

    /**
     * Returns the value that should be used for the shipment option.
     * Arguments will be processed in order, so the last one is most important.
     * Special values are -1 (tristate default) and null, which will be ignored.
     * For booleans: true will prevail over false. valueProcessor returns the value as int (1 for true, 0 for false).
     * For integers: higher values prevail. Strings will be converted to integers, when numeric.
     * For strings: the last non-empty string will prevail.
     * When mixed types are given, output is not guaranteed.
     *
     * @param ...$args
     *
     * @return mixed
     */
    private function valueProcessor(...$args)
    {
        return array_reduce($args, static function ($carry, $item) {
            if (is_bool($item)) {
                $item = (int) $item;
            }

            if (is_numeric($item)) {
                return max((int) $carry, (int) $item);
            }

            return $item ?? $carry;
        }, AbstractSettingsModel::TRISTATE_VALUE_DISABLED);
    }
}
