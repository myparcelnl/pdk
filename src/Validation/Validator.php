<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation;

use MyParcelNL\Pdk\Base\Exception\InvalidCastException;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Helpers\ValidationHelper;

/**
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
class Validator
{
    private const DEFAULT_CARRIER   = 'postnl';
    private const DEFAULT_PLATFORM  = 'myparcel';
    private const VALIDATION_LAYERS = [
        ValidationHelper::CARRIER,
        ValidationHelper::CC,
        ValidationHelper::NAME,
        ValidationHelper::SHIPPING_ZONE,
        ValidationHelper::WEIGHT,
    ];
    private const SAVE_TO_ARRAY     = [
        ValidationHelper::CC,
        ValidationHelper::ID,
        ValidationHelper::LOCATION_CODE,
        ValidationHelper::NAME,
        ValidationHelper::OPTIONS,
        ValidationHelper::REQUIREMENTS,
        ValidationHelper::SCHEMA,
    ];

    /**
     * @var array
     */
    private $mappedArray;

    /**
     * @var PdkOrder
     */
    private $order;

    /**
     * @var string
     */
    private $platform;

    /**
     * @var array
     */
    private $platformSchema;

    /**
     * @var ShipmentOptions
     */
    private $shipmentOptions;

    /**
     * @var array
     */
    private $tempArray = [];

    /**
     * @var \MyParcelNL\Pdk\Validation\Helpers\ValidationHelper
     */
    private $validationHelper;

    /**
     * @var array
     */
    private $validationSchema;

    /**
     * @var array
     */
    private $validationSchemaCopy;

    /**
     * @param  PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        $this->order           = $order;
        $this->shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        // TODO: get correct platform from PDK instance
        $this->platform         = self::DEFAULT_PLATFORM;
        $this->validationHelper = new ValidationHelper();
        $this->validationSchema = Config::get('orderValidator');
    }

    /**
     * @return array
     */
    public function getValidationRules(): array
    {
        $this->platformSchema       = $this->validationHelper->getIndexByValue(
            $this->platform,
            ValidationHelper::NAME,
            $this->validationSchema['platform']
        );
        $this->validationSchemaCopy = $this->platformSchema;

        $orderAttributes = $this->getOrderAttributes();

        if (! $orderAttributes['carrier']['value']) {
            // todo: carrier kiezen zodra er dus geen carrier is dit zal komen uit:
            $orderAttributes['carrier'] = [
                'column' => ValidationHelper::NAME,
                'value'  => self::DEFAULT_CARRIER,
            ];
        }

        foreach ($orderAttributes as $key => $item) {
            $this->getFromSchema($key, $item['column'], $item['value'] ?? '');
        }

        return $this->validationHelper->mergeOptions($this->mappedArray, $this->tempArray);
    }

    /**
     * @return bool
     * @throws InvalidCastException
     */
    public function validate(): bool
    {
        $validationRules = $this->getValidationRules();
        $orderArray      = $this->order->toArray();
        $schema          = $validationRules['schema'] ?? null;

        if ($schema) {
            // TODO: Hier wordt het globale schema opgehaald, en het sub-schema uit de validation dinges erin gemerged, waardoor je niet altijd dezelfde dingen herhaalt en alleen de overrides definieert. Zie de entries waar "CORRECT" bij staat in config/orderValidator.php. Deze data kun je in grote lijnen overnemen uit de api.
            $schema    = array_replace_recursive(Config::get('schema/order'), $schema);
            $validator = new \JsonSchema\Validator();
            $validator->validate($orderArray, (object) $schema);

            $optionsValid = $validator->isValid();
        } else {
            // TODO: dit moet helemaal weg wanneer alles 'schema' gebruikt
            $optionsValid = $this->validateOptions($validationRules[ValidationHelper::OPTIONS]);
        }

        $requirementsValid = $this->validateRequirements($validationRules[ValidationHelper::REQUIREMENTS]);

        return $optionsValid && $requirementsValid;
    }

    /**
     * @param  string $key
     * @param  string $column
     * @param  string $value
     *
     * @return void
     */
    private function getFromSchema(string $key, string $column, string $value): void
    {
        if (is_array($this->platformSchema[$key]) || $key === ValidationHelper::SHIPPING_ZONE) {
            $this->platformSchema = $this->validationHelper->getIndexByValue(
                $value,
                $column,
                $this->platformSchema[$key]
            );

            if ('packageType' !== $key) {
                $this->validationSchemaCopy = $this->platformSchema;
            }

            if ($key === ValidationHelper::CARRIER) {
                $this->mappedArray[$key] = $value;
            }
        }

        $this->recursiveSearch($key, $value);
    }

    /**
     * @return array
     */
    private function getOrderAttributes(): array
    {
        $deliveryOptions = $this->order->deliveryOptions;

        return array_filter([
            'carrier'      => [
                'column' => ValidationHelper::NAME,
                'value'  => $deliveryOptions->getCarrier(),
            ],
            'shippingZone' => [
                'column' => ValidationHelper::CC,
                'value'  => $this->validationHelper->getShippingZone($this->order),
            ],
            'deliveryType' => [
                'column' => ValidationHelper::NAME,
                'value'  => $deliveryOptions->getDeliveryType(),
            ],
            'packageType'  => [
                'column' => ValidationHelper::NAME,
                'value'  => $deliveryOptions->getPackageType(),
            ],
        ]);
    }

    /**
     * @param  string $key
     * @param  string $value
     *
     * @return void
     */
    private function recursiveSearch(string $key, string $value): void
    {
        $path = $this->validationHelper->getArrayPath($value, $this->validationSchemaCopy);

        if (! $path) {
            return;
        }

        $correctPath = $this->validationSchemaCopy;

        for ($i = 0; $i < (count($path) - 1); $i++) {
            $correctPath = $correctPath[$path[$i]];
        }

        if (! in_array($this->tempArray, $correctPath, true)) {
            $temp = [];
            foreach ($correctPath as $pathKey => $pathItem) {
                if (! in_array($pathKey, self::VALIDATION_LAYERS, true)) {
                    $temp[$pathKey] = $pathItem;
                }
            }

            $this->tempArray[$key][] = [
                'path'                 => $path,
                ValidationHelper::DATA => $temp,
            ];
        }

        if (! in_array($key, self::VALIDATION_LAYERS, true)) {
            $this->validationSchemaCopy = $this->validationHelper->removeFromCopySchema(
                $this->validationSchemaCopy,
                $path
            );
            $this->recursiveSearch($key, $value);
        }
    }

    /**
     * @param  array $options
     *
     * @return array
     * @throws InvalidCastException|\MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function validateOptions(array $options): array
    {
        $optionsToValidate = $this->shipmentOptions->toArray();

        if (array_key_exists('labelDescription', $optionsToValidate)) {
            unset($optionsToValidate['labelDescription']);
        }

        $invalidOptions = [];

        foreach ($optionsToValidate as $key => $value) {
            if (! array_key_exists($key, $options)
                || ! in_array(
                    $value,
                    $options[$key][ValidationHelper::ENUM],
                    true
                )) {
                $invalidOptions[] = $key;
            }
        }

        if (count($invalidOptions) > 0) {
            DefaultLogger::warning(
                sprintf('The following options have invalid values: %s', implode(', ', $invalidOptions))
            );
        }

        return $invalidOptions;
    }

    /**
     * @param  array $requirements
     *
     * @return array
     */
    private function validateRequirements(array $requirements): array
    {
        $invalidRequirements = [];

        $checkForBoundaries = [
            ValidationHelper::WEIGHT            => $this->order->physicalProperties->weight,
            ValidationHelper::LABEL_DESCRIPTION => strlen($this->shipmentOptions->labelDescription),
        ];

        $activateOtherOptions = [
            ValidationHelper::AGE_CHECK,
        ];

        foreach ($requirements as $key => $schema) {
            if (array_key_exists($key, $checkForBoundaries)
                && ! $this->validationHelper->isValueWithinBoundaries(
                    $checkForBoundaries[$key],
                    $schema
                )) {
                $invalidRequirements[] = $key;
                continue;
            }

            if ($key === ValidationHelper::LOCATION_CODE && ! $this->order->deliveryOptions->pickupLocation->locationCode) {
                $invalidRequirements[] = $key;
                continue;
            }

            if (($key === ValidationHelper::LARGE_FORMAT)
                && $this->validationHelper->isValueWithinBoundaries(
                    $checkForBoundaries[ValidationHelper::WEIGHT],
                    $schema
                )) {
                $invalidRequirements[] = $key;
                continue;
            }

            if (in_array($key, $activateOtherOptions, true)
                && in_array(
                    $this->shipmentOptions[$key],
                    $schema[ValidationHelper::ENUM],
                    true
                )) {
                foreach ($schema[ValidationHelper::OPTIONS] as $option => $value) {
                    if (! in_array($this->shipmentOptions[$option], $value, true)) {
                        $invalidRequirements[] = $option;
                    }
                }
            }
        }

        if (count($invalidRequirements) > 0) {
            DefaultLogger::warning(
                sprintf('The following requirements haven\'t been met: %s', implode(', ', $invalidRequirements))
            );
        }

        return $invalidRequirements;
    }
}
