<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;
use MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;

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
            self::SHIPMENT_OPTION_KEY => ShipmentOptions::EXTRA_ASSURANCE,
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_EXTRA_ASSURANCE,
            self::PRODUCT_SETTING_KEY => ProductSettings::EXPORT_EXTRA_ASSURANCE,
        ],
        [
            self::SHIPMENT_OPTION_KEY => ShipmentOptions::HIDE_SENDER,
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_HIDE_SENDER,
            self::PRODUCT_SETTING_KEY => ProductSettings::EXPORT_HIDE_SENDER,
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
    protected $countryService;

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface
     */
    protected $currencyService;

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
     * @return PdkOrder
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function calculate(PdkOrder $order): PdkOrder
    {
        $newOrder = clone $order;

        $shipmentOptions = $this->calculateShipmentOptions($newOrder);

        $shipmentOptions->labelDescription = $this->formatLabelDescription($order);

        if (AbstractSettingsModel::TRISTATE_VALUE_ENABLED === $shipmentOptions->insurance) {
            $shipmentOptions->insurance = $this->calculateInsurance($order);
        }

        $newOrder->deliveryOptions->shipmentOptions = $shipmentOptions;

        return $newOrder;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return int
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function calculateInsurance(PdkOrder $order): int
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
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\ShipmentOptions
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function calculateShipmentOptions(PdkOrder $order): ShipmentOptions
    {
        /** @var CarrierSettings $carrierSettings */
        $carrierSettings = null;
        /** @var ProductSettings $productSettings */
        $productSettings = null;

        $shipmentOptions = $order->deliveryOptions->shipmentOptions;

        foreach (self::SETTING_KEYS as $keys) {
            $key = $keys[self::SHIPMENT_OPTION_KEY];

            // If value is already set, keep it.
            if (null !== $shipmentOptions->getAttribute($key)) {
                continue;
            }

            $productSettings = $productSettings ?? $this->mergeProductSettings($order);

            $value = $productSettings->getAttribute($keys[self::PRODUCT_SETTING_KEY]);

            if (AbstractSettingsModel::TRISTATE_VALUE_DEFAULT === $value) {
                $carrierSettings = $carrierSettings ?? CarrierSettings::fromCarrier($order->deliveryOptions->carrier);

                $value = $carrierSettings->getAttribute($keys[self::CARRIER_SETTING_KEY]) ?? false;
            }

            $shipmentOptions->setAttribute($key, $value);
        }

        return $shipmentOptions;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return string
     */
    protected function formatLabelDescription(PdkOrder $order): string
    {
        $description = $order->deliveryOptions->shipmentOptions->labelDescription
            ?? Settings::get(LabelSettings::DESCRIPTION, LabelSettings::ID)
            ?? '';

        $createString = static function (string $key) use ($order): string {
            return implode(', ', Utils::filterNull(Arr::pluck($order->lines->all(), $key)));
        };

        return preg_replace_callback_array([
            '/\[ORDER_ID\]/' => static function () use ($order) {
                return $order->externalIdentifier;
            },

            '/\[CUSTOMER_NOTE\]/' => static function () use ($order) {
                return $order->notes->firstWhere('author', OrderNote::AUTHOR_CUSTOMER)->note ?? '';
            },

            '/\[PRODUCT_ID\]/' => static function () use ($createString) {
                return $createString('product.externalIdentifier');
            },

            '/\[PRODUCT_NAME\]/' => static function () use ($createString) {
                return $createString('product.name');
            },

            '/\[PRODUCT_SKU\]/' => static function () use ($createString) {
                return $createString('product.sku');
            },

            '/\[PRODUCT_EAN\]/' => static function () use ($createString) {
                return $createString('product.ean');
            },

            '/\[PRODUCT_QTY\]/' => static function () use ($order) {
                return $order->lines->sum('quantity');
            },
        ], $description);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return ProductSettings
     */
    protected function mergeProductSettings(PdkOrder $order): ProductSettings
    {
        $productSettings = new ProductSettings();

        foreach (self::SETTING_KEYS as $option) {
            $productSettingsKey = $option[self::PRODUCT_SETTING_KEY];

            $values = $order->lines
                ->pluck(sprintf('product.settings.%s', $productSettingsKey))
                ->filter(function ($value) {
                    return is_int($value)
                        && $value >= AbstractSettingsModel::TRISTATE_VALUE_DEFAULT
                        && $value <= AbstractSettingsModel::TRISTATE_VALUE_ENABLED;
                })
                ->all();

            $productSettings->setAttribute($productSettingsKey, $this->resolveTriStateValues(...$values));
        }

        return $productSettings;
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
     * @param  int ...$args
     *
     * @return int
     */
    private function resolveTriStateValues(int ...$args): int
    {
        return array_reduce($args, static function (int $carry, int $item) {
            return max($carry, $item);
        }, AbstractSettingsModel::TRISTATE_VALUE_DEFAULT);
    }
}
