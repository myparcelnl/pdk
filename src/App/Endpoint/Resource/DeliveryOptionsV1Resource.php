<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Resource;

use ArrayObject;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedResource;
use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CollectDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\HideSenderDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PriorityDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\ReceiptCodeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SameDayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SaturdayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\App\Options\Definition\TrackedDefinition;
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
         * Handle the inversion of tracked to no_tracking
         * New tracking implementation: When tracked option is not present or explicitly enabled we do nothing (tracking is enabled by default in the order service)
         * Only when tracking is explicitly disabled we include the "noTracking" option with an ADR-0013 compliant empty object as value
         */
        $trackedKey = (new TrackedDefinition())->getShipmentOptionsKey();

        if ($shipmentOptions->{$trackedKey} === TriStateService::DISABLED) {
            $formattedOptions[$orderApiShipmentOptions['no_tracking']] = new ArrayObject();
        }

        $insuranceKey        = (new InsuranceDefinition())->getShipmentOptionsKey();
        $labelDescriptionKey = ShipmentOptions::LABEL_DESCRIPTION;

        $optionMap = [
            (new AgeCheckDefinition())->getShipmentOptionsKey()         => $orderApiShipmentOptions['requires_age_verification'],
            (new SignatureDefinition())->getShipmentOptionsKey()        => $orderApiShipmentOptions['requires_signature'],
            (new OnlyRecipientDefinition())->getShipmentOptionsKey()    => $orderApiShipmentOptions['recipient_only_delivery'],
            (new LargeFormatDefinition())->getShipmentOptionsKey()      => $orderApiShipmentOptions['oversized_package'],
            (new DirectReturnDefinition())->getShipmentOptionsKey()     => $orderApiShipmentOptions['print_return_label_at_drop_off'],
            (new HideSenderDefinition())->getShipmentOptionsKey()       => $orderApiShipmentOptions['hide_sender'],
            $labelDescriptionKey                                        => $orderApiShipmentOptions['custom_label_text'],
            (new PriorityDeliveryDefinition())->getShipmentOptionsKey() => $orderApiShipmentOptions['priority_delivery'],
            (new ReceiptCodeDefinition())->getShipmentOptionsKey()      => $orderApiShipmentOptions['requires_receipt_code'],
            (new SameDayDeliveryDefinition())->getShipmentOptionsKey()  => $orderApiShipmentOptions['same_day_delivery'],
            (new SaturdayDeliveryDefinition())->getShipmentOptionsKey() => $orderApiShipmentOptions['saturday_delivery'],
            (new CollectDefinition())->getShipmentOptionsKey()          => $orderApiShipmentOptions['scheduled_collection'],
        ];

        foreach ($filteredOptions as $key => $value) {
            if ($key === $insuranceKey) {
                // Insurance amount converted to integer micro as per ADR-0014
                $amount = ((int)$value) * 1_000_000;
                $currency = new Currency();
                $formattedOptions[$orderApiShipmentOptions['insurance']] = ['amount' => $amount, 'currency' => $currency->currency];
            } elseif ($key === $labelDescriptionKey) {
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
