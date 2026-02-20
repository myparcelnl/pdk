<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Resource;

use Alcohol\ISO4217;
use ArrayObject;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedResource;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\Carrier as OrderApiCarrier;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\DeliveryType as OrderApiDeliveryType;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\PackageType as OrderApiPackageType;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\ShipmentOptions as ModelShipmentOptions;
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
            'carrier' => self::formatCarrier($this->model->carrier->name),
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
        // Include only explcitly enabled options - we assume any inherited options were resolved before being passed her
        $filteredOptions = array_filter(
            $shipmentOptions->toArray(),
            fn($value) => $value && $value !== TriStateService::INHERIT
        );

        $formattedOptions = [];

        // AttributeMap is a lower snake_case to camelCase mapping of the shipment options, we can use it to convert our keys to the expected format
        $orderApiShipmentOptions = ModelShipmentOptions::attributeMap();

        /*
         * Handle the inversion of tracked to no_tracking
         * New tracking implementation: When tracked option is not present or explicitly enabled we do nothing (tracking is enabled by default in the order service)
         * Only when tracking is explicitly disabled we include the "noTracking" option with an ADR-0013 compliant empty object as value
         */
        if ($shipmentOptions->tracked === TriStateService::DISABLED) {
            $formattedOptions[$orderApiShipmentOptions['no_tracking']] = new ArrayObject();
        }

        $optionMap = [
            ShipmentOptions::AGE_CHECK => $orderApiShipmentOptions['requires_age_verification'],
            ShipmentOptions::SIGNATURE => $orderApiShipmentOptions['requires_signature'],
            ShipmentOptions::ONLY_RECIPIENT => $orderApiShipmentOptions['recipient_only_delivery'],
            ShipmentOptions::LARGE_FORMAT => $orderApiShipmentOptions['oversized_package'],
            ShipmentOptions::DIRECT_RETURN => $orderApiShipmentOptions['print_return_label_at_drop_off'],
            ShipmentOptions::HIDE_SENDER => $orderApiShipmentOptions['hide_sender'],
            ShipmentOptions::LABEL_DESCRIPTION => $orderApiShipmentOptions['custom_label_text'],
            ShipmentOptions::PRIORITY_DELIVERY => $orderApiShipmentOptions['priority_delivery'],
            ShipmentOptions::RECEIPT_CODE => $orderApiShipmentOptions['requires_receipt_code'],
            ShipmentOptions::SAME_DAY_DELIVERY => $orderApiShipmentOptions['same_day_delivery'],
            ShipmentOptions::SATURDAY_DELIVERY => $orderApiShipmentOptions['saturday_delivery'],
            ShipmentOptions::COLLECT => $orderApiShipmentOptions['scheduled_collection'],
        ];

        foreach ($filteredOptions as $key => $value) {
            if ($key === ShipmentOptions::INSURANCE) {
                // Insurance amount converted to integer micro as per ADR-0014
                $amount = is_numeric($value) ? (int) ($value * 1000000) : (int) $value;
                $currency = (new ISO4217())->getByAlpha3('EUR'); // Assuming EUR, adjust in the future as needed
                $formattedOptions[$orderApiShipmentOptions['insurance']] = ['amount' => $amount, 'currency' => $currency['alpha3']];
            } elseif ($key === ShipmentOptions::LABEL_DESCRIPTION) {
                // Custom label text option needs to be formatted as an object with a "text" property
                $formattedOptions[$orderApiShipmentOptions['custom_label_text']] = ['text' => (string) $value];
            } else {
                $mappedKey = null;
                // Map our key to Order API service
                if (in_array($key, $orderApiShipmentOptions, true)) {
                    // 1:1 match with Order API shipment options, use it directly
                    $mappedKey = $key;
                } else if (in_array(Str::lower(Str::snake($key)), $orderApiShipmentOptions, true)) {
                    // If no 1:1 match, attempt to convert to snake_case and check again
                    $mappedKey = Str::lower(Str::snake($key));
                } else if (array_key_exists($key, $optionMap)) {
                    // If no 1:1 match, check our mapping
                    $mappedKey = $optionMap[$key];
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
            Carrier::CARRIER_BOL_COM_LEGACY_NAME => OrderApiCarrier::BOL,
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
        // If the package type already equals one of the order service constants, return it directly
        if (\in_array($packageType, OrderApiPackageType::getAllowableEnumValues(), true)) {
            return $packageType;
        } else {
            // Attempt to convert it to SCREAMING_SNAKE_CASE and check again
            $convertedName = Str::upper(Str::snake($packageType));
            if (\in_array($convertedName, OrderApiPackageType::getAllowableEnumValues(), true)) {
                return $convertedName;
            }
        }
        // Otherwise, use our mapping
        $packageTypeMapping = [
            DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME => OrderApiPackageType::PACKAGE,
            DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME => OrderApiPackageType::MAILBOX,
            DeliveryOptions::PACKAGE_TYPE_LETTER_NAME => OrderApiPackageType::UNFRANKED,
            DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME => OrderApiPackageType::DIGITAL_STAMP,
            DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME => OrderApiPackageType::SMALL_PACKAGE,
        ];

        return \array_key_exists($packageType, $packageTypeMapping) ? $packageTypeMapping[$packageType] : null;
    }

    /**
     * Convert delivery type to CONSTANT_CASE format using existing constants.
     */
    private static function formatDeliveryType(string $deliveryType): ?string
    {
        // If the delivery type already equals one of the order service constants, return it directly
        if (\in_array($deliveryType, OrderApiDeliveryType::getAllowableEnumValues(), true)) {
            return $deliveryType;
        } else {
            // Attempt to convert it to SCREAMING_SNAKE_CASE and check again
            $convertedName = Str::upper(Str::snake($deliveryType));
            if (\in_array($convertedName, OrderApiDeliveryType::getAllowableEnumValues(), true)) {
                return $convertedName;
            }
        }
        // Otherwise, use our mapping
        $deliveryTypeMapping = [
            DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME => OrderApiDeliveryType::STANDARD_DELIVERY,
            DeliveryOptions::DELIVERY_TYPE_MORNING_NAME => OrderApiDeliveryType::MORNING_DELIVERY,
            DeliveryOptions::DELIVERY_TYPE_EVENING_NAME => OrderApiDeliveryType::EVENING_DELIVERY,
            DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME => OrderApiDeliveryType::PICKUP_DELIVERY,
            DeliveryOptions::DELIVERY_TYPE_EXPRESS_NAME => OrderApiDeliveryType::EXPRESS_DELIVERY,
        ];

        return \array_key_exists($deliveryType, $deliveryTypeMapping) ? $deliveryTypeMapping[$deliveryType] : null;
    }
}
