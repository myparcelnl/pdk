<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Resource;

use ArrayObject;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedResource;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\NoTrackingDefinition;
use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\Carrier as OrderApiCarrier;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\DeliveryType as OrderApiDeliveryType;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\PackageType as OrderApiPackageType;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\ShipmentOptions as ModelShipmentOptions;
use MyParcelNL\Sdk\Services\Mapping\ApiMapperService;
use MyParcelNL\Sdk\Services\Mapping\ShipmentOptionMapper;
use MyParcelNL\Sdk\Support\Str;

/**
 * API v1 response formatter for delivery options data.
 *
 * Formats order delivery options according to v1 API specifications.
 *
 * @property DeliveryOptions $model
 */
final class DeliveryOptionsV1Resource extends AbstractVersionedResource
{
    private const ORDER_API_DELIVERY_TYPE_SUFFIX = '_DELIVERY';

    /**
     * Get the API version this resource handles.
     */
    public static function getVersion(): int
    {
        return 1;
    }

    /**
     * Format data for API v1.
     */
    public function format(): array
    {
        return [
            'carrier' => self::formatCarrier($this->model->carrier->carrier),
            'packageType' => $this->model->packageType ? self::formatPackageType($this->model->packageType) : null,
            'deliveryType' => $this->model->deliveryType ? self::formatDeliveryType($this->model->deliveryType) : null,
            'shipmentOptions' => self::formatShipmentOptions($this->model->shipmentOptions),
            // format date as ISO 8601 string or null
            'date' => $this->model->date ? $this->model->date->format('c') : null,
            'pickupLocation' => $this->model->pickupLocation ? self::formatPickupLocation($this->model->pickupLocation) : null,
        ];
    }

    /**
     * Format shipment options following API standards.
     * Returns an associative array with camelCase keys and values.
     *
     * We assume that inherited options were resolved before passing them here.
     */
    private static function formatShipmentOptions(ShipmentOptions $shipmentOptions): array
    {
        // Include only explicitly enabled options - we assume any inherited options were resolved before being passed here
        $filteredOptions = array_filter(
            $shipmentOptions->toArray(),
            fn($value) => $value && $value !== TriStateService::INHERIT
        );

        $formattedOptions = [];

        // AttributeMap is a lower snake_case to camelCase mapping of the shipment options, we can use it to convert our keys to the expected format
        $orderApiShipmentOptions = ModelShipmentOptions::attributeMap();

        /*
         * Tracking is enabled by default in the order service, so the key is only sent when a merchant
         * explicitly opted out. The value is an ADR-0013 compliant empty object.
         */
        $noTrackingKey = (new NoTrackingDefinition())->getShipmentOptionsKey();

        if ($shipmentOptions->{$noTrackingKey} === TriStateService::ENABLED) {
            $formattedOptions[$orderApiShipmentOptions['no_tracking']] = new ArrayObject();
        }

        $insuranceKey        = (new InsuranceDefinition())->getShipmentOptionsKey();
        $labelDescriptionKey = ShipmentOptions::LABEL_DESCRIPTION;

        $optionMapper = new ShipmentOptionMapper();
        $returnKey    = (new DirectReturnDefinition())->getShipmentOptionsKey();

        foreach ($filteredOptions as $key => $value) {
            if ($key === $insuranceKey) {
                // Insurance is stored in cents; convert to integer micros (1 cent = 10_000 micros).
                $amount = ((int)$value) * 10_000;
                $currency = new Currency();
                $formattedOptions[$orderApiShipmentOptions['insurance']] = ['amount' => $amount, 'currency' => $currency->currency];
            } elseif ($key === $labelDescriptionKey) {
                // Custom label text option needs to be formatted as an object with a "text" property
                $formattedOptions[$orderApiShipmentOptions['custom_label_text']] = ['text' => (string) $value];
            } else {
                if (in_array($key, $orderApiShipmentOptions, true)) {
                    $mappedKey = $key;
                } else {
                    // This resource uses return for label printing at drop-off, unlike capabilities.
                    $property = $key === $returnKey
                        ? 'print_return_label_at_drop_off'
                        : $optionMapper->v2PropertyFromName(Str::snake($key));
                    $mappedKey = $orderApiShipmentOptions[$property ?? Str::snake($key)] ?? null;
                }
                // Format as an empty object as per ADR-0013
                if ($mappedKey) {
                    $formattedOptions[$mappedKey] = new ArrayObject();
                } else {
                    Logger::warning("Unmapped shipment option key: {$key} with value: {$value} - this option will be skipped in the API response");
                }
            }
        }

        return $formattedOptions;
    }

    /**
     * Format pickup location information.
     */
    private static function formatPickupLocation(RetailLocation $pickupLocation): array
    {
        return [
            'locationCode' => $pickupLocation->locationCode,
            'locationName' => $pickupLocation->locationName,
            'retailNetworkId' => $pickupLocation->retailNetworkId,
            'type' => $pickupLocation->type,
            'address' => [
                'street' => $pickupLocation->street,
                'number' => $pickupLocation->number,
                'numberSuffix' => $pickupLocation->numberSuffix,
                'postalCode' => $pickupLocation->postalCode,
                'boxNumber' => $pickupLocation->boxNumber,
                'city' => $pickupLocation->city,
                'cc' => $pickupLocation->cc,
                'state' => $pickupLocation->state,
                'region' => $pickupLocation->region,
            ],
        ];
    }

    /**
     * Convert carrier name to CONSTANT_CASE format for Order Service.
     */
    private static function formatCarrier(string $carrierName): string
    {
        // If the carrier name already equals on of the order service constants, return it directly
        if (\in_array($carrierName, OrderApiCarrier::getAllowableEnumValues(), true)) {
            return $carrierName;
        } else {
            // Attempt to convert it to SCREAMING_SNAKE_CASE and check again
            $convertedName = Str::upper(Str::snake($carrierName));
            if (\in_array($convertedName, OrderApiCarrier::getAllowableEnumValues(), true)) {
                return $convertedName;
            }
        }
        // Otherwise, use our mapping
        $carrierMapping = [
            Carrier::CARRIER_POSTNL_LEGACY_NAME => OrderApiCarrier::POSTNL,
            Carrier::CARRIER_BPOST_LEGACY_NAME => OrderApiCarrier::BPOST,
            Carrier::CARRIER_CHEAP_CARGO_LEGACY_NAME => OrderApiCarrier::CHEAP_CARGO,
            Carrier::CARRIER_DPD_LEGACY_NAME => OrderApiCarrier::DPD,
            Carrier::CARRIER_DHL_FOR_YOU_LEGACY_NAME => OrderApiCarrier::DHL_FOR_YOU,
            Carrier::CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME => OrderApiCarrier::DHL_PARCEL_CONNECT,
            Carrier::CARRIER_DHL_EUROPLUS_LEGACY_NAME => OrderApiCarrier::DHL_EUROPLUS,
            Carrier::CARRIER_UPS_STANDARD_LEGACY_NAME => OrderApiCarrier::UPS_STANDARD,
            Carrier::CARRIER_UPS_EXPRESS_SAVER_LEGACY_NAME => OrderApiCarrier::UPS_EXPRESS_SAVER,
            Carrier::CARRIER_GLS_LEGACY_NAME => OrderApiCarrier::GLS,
            Carrier::CARRIER_BRT_LEGACY_NAME => OrderApiCarrier::BRT,
            Carrier::CARRIER_TRUNKRS_LEGACY_NAME => OrderApiCarrier::TRUNKRS,
        ];

        if (\array_key_exists($carrierName, $carrierMapping)) {
            return $carrierMapping[$carrierName];
        }

        throw new \InvalidArgumentException("Unknown carrier name: {$carrierName} - cannot be mapped to Order API carrier");
    }

    /**
     * Convert package type to CONSTANT_CASE format using existing constants.
     */
    private static function formatPackageType(string $packageType): ?string
    {
        $allowedValues = OrderApiPackageType::getAllowableEnumValues();

        if (in_array($packageType, $allowedValues, true)) {
            return $packageType;
        }

        $mappedName = ApiMapperService::forPackageType()->v2NameFromLegacyName($packageType);

        if (in_array($mappedName, $allowedValues, true)) {
            return $mappedName;
        }

        $convertedName = Str::upper(Str::snake($packageType));

        return in_array($convertedName, $allowedValues, true) ? $convertedName : null;
    }

    /**
     * Convert delivery type to CONSTANT_CASE format using existing constants.
     */
    private static function formatDeliveryType(string $deliveryType): ?string
    {
        $allowedValues = OrderApiDeliveryType::getAllowableEnumValues();

        // If the delivery type already equals one of the order service constants, return it directly
        if (\in_array($deliveryType, $allowedValues, true)) {
            return $deliveryType;
        }

        $mappedName = ApiMapperService::forDeliveryType()->v2NameFromLegacyName($deliveryType);

        if (in_array($mappedName, $allowedValues, true)) {
            return $mappedName;
        }

        // Order API types can exist without a matching Core API definition.
        // Attempt to convert it to SCREAMING_SNAKE_CASE and check again
        $convertedName = Str::upper(Str::snake($deliveryType));
        if (\in_array($convertedName, $allowedValues, true)) {
            return $convertedName;
        }

        // Every Order API delivery type carries the suffix, so same_day => SAME_DAY_DELIVERY
        $suffixedName = $convertedName . self::ORDER_API_DELIVERY_TYPE_SUFFIX;
        if (\in_array($suffixedName, $allowedValues, true)) {
            return $suffixedName;
        }

        Logger::warning("Unmapped delivery type: {$deliveryType} - this delivery type will be null in the API response");

        return null;
    }
}
