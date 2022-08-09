<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation;

use MyParcelNL\Pdk\Base\Model\InvalidCastException;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Collection\DefaultLogger;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Helpers\ValidationHelper;

/**
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
class Validator
{
    public const  DEFAULT_CARRIER          = 'postnl';
    public const  DEFAULT_PLATFORM         = 'myparcel';
    public const  DO_NOT_DELETE_BUT_REMOVE = [
        ValidationHelper::CARRIER,
        ValidationHelper::CC,
        ValidationHelper::NAME,
        ValidationHelper::SHIPPING_ZONE,
        ValidationHelper::WEIGHT,
    ];
    public const  SAVE_TO_ARRAY            = [
        ValidationHelper::CC,
        ValidationHelper::ID,
        ValidationHelper::LOCATION_CODE,
        ValidationHelper::NAME,
        ValidationHelper::OPTIONS,
        ValidationHelper::REQUIREMENTS,
    ];

    /**
     * @var
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
     * @var
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
     * @var ValidationHelper
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
     * @param PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        $this->order           = $order;
        $this->shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        // TODO: get correct platform from PDK instance
        $this->platform         = self::DEFAULT_PLATFORM;
        $this->validationHelper = new ValidationHelper();
        $this->validationSchema = ValidationSchema::VALIDATION_SCHEMA;
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
        $orderAttributes            = $this->getOrderAttributes();

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
        $validationRules     = $this->getValidationRules();
        $invalidOptions      = $this->validateOptions($validationRules[ValidationHelper::OPTIONS]);
        $invalidRequirements = $this->validateRequirements($validationRules[ValidationHelper::REQUIREMENTS]);

        return $invalidOptions && $invalidRequirements;
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
        if (is_array($this->platformSchema[$key]) || $key===ValidationHelper::SHIPPING_ZONE) {
            $this->platformSchema = $this->validationHelper->getIndexByValue(
                $value,
                $column,
                $this->platformSchema[$key]
            );

            if ($key!=='packageType') {
                $this->validationSchemaCopy = $this->platformSchema;
            }

            if ($key===ValidationHelper::CARRIER) {
                $this->mappedArray['carrier'] = $value;
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
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    private function recursiveSearch(string $key, string $value): void
    {
        $path = $this->validationHelper->getArrayPath($value, $this->validationSchemaCopy);

        if ($path) {
            $correctPath = $this->validationSchemaCopy;

            $count = count($path) - 1;
            for ($i = 0; $i < $count; $i++) {
                $correctPath = $correctPath[$path[$i]];
            }

            if (! in_array($this->tempArray, $correctPath, true)) {
                $temp = [];
                foreach ($correctPath as $pathKey => $pathItem) {
                    if (in_array($pathKey, self::SAVE_TO_ARRAY, true)) {
                        $temp[$pathKey] = $pathItem;
                    }
                }

                $this->tempArray[$key][] = [
                    'path'                 => $path,
                    ValidationHelper::DATA => $temp,
                ];
            }

            if (! in_array($key, self::DO_NOT_DELETE_BUT_REMOVE, true)) {
                $this->validationSchemaCopy = $this->validationHelper->removeFromCopySchema(
                    $this->validationSchemaCopy,
                    $path
                );
                $this->recursiveSearch($key, $value);
            }
        }
    }

    /**
     * @param array $options
     *
     * @return array
     * @throws InvalidCastException
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

        foreach ($requirements as $key => $properties) {
            if (array_key_exists($key, $checkForBoundaries)
                && ! $this->validationHelper->isValueWithinBoundaries(
                    $checkForBoundaries[$key],
                    $properties
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
                    $properties
                )) {
                $invalidRequirements[] = $key;
                continue;
            }

            if (in_array($key, $activateOtherOptions, true)
                && in_array(
                    $this->shipmentOptions[$key],
                    $properties[ValidationHelper::ENUM],
                    true
                )) {
                foreach ($properties[ValidationHelper::OPTIONS] as $option => $value) {
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
