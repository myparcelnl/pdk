<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use Exception;
use MyParcelNL\Pdk\Base\Exception\InvalidCastException;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Model\PdkOrderLine;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Sdk\src\Support\Arr;

class ShipmentOptionsService
{
    public const  SETTING_KEYS        = [
        [
            self::SHIPMENT_OPTION_KEY => 'ageCheck',
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_AGE_CHECK,
            self::PRODUCT_SETTING_KEY => ProductSettings::EXPORT_AGE_CHECK,
        ],
        [
            self::SHIPMENT_OPTION_KEY => 'insurance',
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_INSURANCE,
            self::PRODUCT_SETTING_KEY => ProductSettings::EXPORT_INSURANCE,
        ],
        [
            self::SHIPMENT_OPTION_KEY => 'largeFormat',
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_LARGE_FORMAT,
            self::PRODUCT_SETTING_KEY => ProductSettings::EXPORT_LARGE_FORMAT,
        ],
        [
            self::SHIPMENT_OPTION_KEY => 'onlyRecipient',
            self::CARRIER_SETTING_KEY => CarrierSettings::ALLOW_ONLY_RECIPIENT,
            self::PRODUCT_SETTING_KEY => ProductSettings::ALLOW_ONLY_RECIPIENT,
        ],
        [
            self::SHIPMENT_OPTION_KEY => 'return',
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_RETURN_SHIPMENTS,
            self::PRODUCT_SETTING_KEY => ProductSettings::RETURN_SHIPMENTS,
        ],
        [
            self::SHIPMENT_OPTION_KEY => 'sameDayDelivery',
            self::CARRIER_SETTING_KEY => CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
        ],
        [
            self::SHIPMENT_OPTION_KEY => 'signature',
            self::CARRIER_SETTING_KEY => CarrierSettings::EXPORT_SIGNATURE,
            self::PRODUCT_SETTING_KEY => ProductSettings::ALLOW_SIGNATURE,
        ],
    ];
    private const SHIPMENT_OPTION_KEY = 'shipmentOption';
    private const CARRIER_SETTING_KEY = 'carrierSetting';
    private const PRODUCT_SETTING_KEY = 'productSetting';

    /**
     * @var CarrierSettings
     */
    private $carrierSettings;

    /**
     * @var \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     */
    private $order;

    public function __construct(PdkOrder $order)
    {
        $this->order           = $order;
        $carrierName           = $order->deliveryOptions->getCarrier() ?? Platform::get('defaultCarrier');
        $this->carrierSettings = $this->getCarrierSettings($carrierName);
    }

    /**
     * Shipment options set on the order have priority.
     * Shipment options not set on the order, will be merged from the order lines and the carrier settings.
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     */
    public function calculate(): PdkOrder
    {
        $order           = $this->order;
        $lines           = $order->getLines();
        $attributes      = $order->getAttributes();
        $shipmentOptions = $order->getDeliveryOptions()
            ->getShipmentOptions();
        try {
            $shipmentOptionsArray = $this->mergeOrderLines($lines, $shipmentOptions->toArray());
        } catch (InvalidCastException $e) {
            // TODO log error?
            return $order;
        }

        $order->fill(
            [
                'deliveryOptions' => [
                    'shipmentOptions' => $shipmentOptionsArray,
                ],
            ] + $attributes
        );

        return $order;
    }

    private function calculateInsurance(): int
    {
        try {
            $validator = $this->order->getValidator();
        } catch (Exception $e) {
            // todo log error?
            return 0;
        }

        $fromAmount = 100 * ($this->carrierSettings[CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT] ?? 0);
        $grandTotal = $this->order->getOrderPriceAfterVat();

        $availableInsuranceAmounts = $validator->insuranceAmounts();

        if ($grandTotal < $fromAmount) {
            return $this->getAllowedValueNearThreshold(0, $availableInsuranceAmounts);
        }

        $insuranceUpToKey = static function (string $cc): string {
            if ($cc === Platform::get('localCountry')) {
                return CarrierSettings::EXPORT_INSURANCE_UP_TO;
            }

            if ($cc === CountryService::CC_BE) {
                return CarrierSettings::EXPORT_INSURANCE_UP_TO_BE;
            }

            return CarrierSettings::EXPORT_INSURANCE_UP_TO_EU;
        };

        $cc         = $this->order->recipient->cc ?? Platform::get('localCountry');
        $upToAmount = 100 * ($this->carrierSettings[$insuranceUpToKey($cc)] ?? 0);

        return $this->getAllowedValueNearThreshold(min($grandTotal, $upToAmount), $availableInsuranceAmounts);
    }

    /**
     * @param  int   $threshold
     * @param  array $allowedValues this must be an indexed array with values sorted from low to high
     *
     * @return int lowest allowed value that is higher than or equal to threshold, or the highest allowed value
     */
    private function getAllowedValueNearThreshold(int $threshold, array $allowedValues): int
    {
        foreach ($allowedValues as $allowedValue) {
            if ($allowedValue < $threshold) {
                continue;
            }
            return $allowedValue;
        }

        return Arr::last($allowedValues);
    }

    /**
     * @param  string $carrierName
     *
     * @return \MyParcelNL\Pdk\Settings\Model\CarrierSettings
     */
    private function getCarrierSettings(string $carrierName): CarrierSettings
    {
        $settings = Settings::get(sprintf('%s.%s', CarrierSettings::ID, $carrierName));

        return new CarrierSettings($settings);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Collection\PdkOrderLineCollection $lines
     *
     * @return array
     */
    private function getFromOrderLines(PdkOrderLineCollection $lines): array
    {
        $mergedSettings = [];

        foreach ($lines as $line) {
            if (! $line instanceof PdkOrderLine
                || ! ($product = $line->getProduct())
                || ! ($productSettings = $product->getSettings())) {
                continue;
            }

            try {
                $settingsArray = $productSettings->toArray();
            } catch (InvalidCastException $e) {
                // TODO log error?
                continue;
            }

            $mergedSettings = $this->mergeProductSettings($settingsArray, $mergedSettings);
        }
        return $mergedSettings;
    }

    /**
     * values in $shipmentOptions have priority over calculated values from $lines
     *
     * @param  \MyParcelNL\Pdk\Plugin\Collection\PdkOrderLineCollection $lines
     * @param  array                                                    $shipmentOptions
     *
     * @return array
     */
    private function mergeOrderLines(PdkOrderLineCollection $lines, array $shipmentOptions): array
    {
        $productSettings = $this->getFromOrderLines($lines);

        foreach (self::SETTING_KEYS as $keys) {
            $shipmentOption = $keys[self::SHIPMENT_OPTION_KEY];
            $carrierSetting = $keys[self::CARRIER_SETTING_KEY];

            if (isset($shipmentOptions[$shipmentOption])) {
                continue;
            }

            $fromSettings = $this->carrierSettings[$carrierSetting] ?? null;
            $fromProducts = $productSettings[$shipmentOption];

            $shipmentOptions[$shipmentOption] = $this->valueProcessor(0, $fromSettings, $fromProducts);
        }

        if (1 === $shipmentOptions['insurance']) {
            $shipmentOptions['insurance'] = $this->calculateInsurance();
        }

        return $shipmentOptions;
    }

    /**
     * @param  array $settings
     * @param  array $return
     *
     * @return array
     */
    private function mergeProductSettings(array $settings, array $return): array
    {
        foreach (self::SETTING_KEYS as $keys) {
            $shipmentOption = $keys[self::SHIPMENT_OPTION_KEY];
            $productSetting = $keys[self::PRODUCT_SETTING_KEY] ?? null;

            switch ($shipmentOption) {
                case 'sameDayDelivery':
                    $productValue = 0 !== ($settings[ProductSettings::DROP_OFF_DELAY] ?? 0) ? 0 : -1;
                    break;
                default:
                    $productValue = $settings[$productSetting] ?? null;
            }

            $return[$shipmentOption] = $this->valueProcessor(0, $productValue, $return[$shipmentOption] ?? null);
        }

        return $return;
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
     * @param $initial mixed default value that will be returned if other values are ignored.
     * @param ...$args
     *
     * @return mixed
     */
    private function valueProcessor($initial, ...$args)
    {
        return array_reduce($args, static function ($carry, $item) {
            if (is_bool($item)) {
                $item = (int) $item;
            }

            if (is_numeric($item)) {
                return max((int) $carry, (int) $item);
            }

            return $item ?? $carry;
        }, $initial);
    }
}
