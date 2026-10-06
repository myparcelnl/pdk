<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Resource;

use ArrayObject;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedResource;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\NoTrackingDefinition;
use MyParcelNL\Pdk\Base\Model\Currency;
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
    private static function formatShipmentOptions(ShipmentOptions $shipmentOptions): object
    {
        // Include only explicitly enabled options - we assume any inherited options were resolved before being passed here
        $filteredOptions = array_filter(
            $shipmentOptions->toArray(),
            fn($value) => $value && $value !== TriStateService::INHERIT
        );

        $formattedOptions = new ArrayObject();

        // AttributeMap is a lower snake_case to camelCase mapping of the shipment options, we can use it to convert our keys to the expected format
        $orderApiShipmentOptions = ModelShipmentOptions::attributeMap();

        /*
         * Tracking is enabled by default in the order service, so the key is only sent when a merchant
         * explicitly opted out. The value is an ADR-0013 compliant empty object.
         */
        $noTrackingKey = (new NoTrackingDefinition())->getShipmentOptionsKey();

        if ($shipmentOptions->{$noTrackingKey} === TriStateService::ENABLED) {
            $formattedOptions->offsetSet($orderApiShipmentOptions['no_tracking'], new ArrayObject());
        }

        $insuranceKey        = (new InsuranceDefinition())->getShipmentOptionsKey();
        $labelDescriptionKey = ShipmentOptions::LABEL_DESCRIPTION;

        $optionMapper = new ShipmentOptionMapper();

        foreach ($filteredOptions as $key => $value) {
            if ($key === $insuranceKey) {
                // Insurance is stored in cents; convert to integer micros (1 cent = 10_000 micros).
                $amount = ((int)$value) * 10_000;
                $currency = new Currency();
                $formattedOptions->offsetSet($orderApiShipmentOptions['insurance'], new ArrayObject(['amount' => $amount, 'currency' => $currency->currency]));
            } elseif ($key === $labelDescriptionKey) {
                // Custom label text option needs to be formatted as an object with a "text" property
                $formattedOptions->offsetSet($orderApiShipmentOptions['custom_label_text'], new ArrayObject(['text' => (string) $value]));
            } else {
                if (in_array($key, $orderApiShipmentOptions, true)) {
                    $mappedKey = $key;
                } else {
                    $property  = $optionMapper->v2PropertyFromName(Str::snake($key));
                    $mappedKey = $orderApiShipmentOptions[$property ?? Str::snake($key)] ?? null;
                }
                // Format as an empty object as per ADR-0013
                if ($mappedKey) {
                    $formattedOptions->offsetSet($mappedKey, new ArrayObject());
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
        $formatted = self::toOrderApiValue(
            $carrierName,
            ApiMapperService::forCarrier(),
            OrderApiCarrier::getAllowableEnumValues()
        );

        if (null === $formatted) {
            throw new \InvalidArgumentException("Unknown carrier name: {$carrierName} - cannot be mapped to Order API carrier");
        }

        return $formatted;
    }

    /**
     * Convert package type to CONSTANT_CASE format for Order Service.
     */
    private static function formatPackageType(string $packageType): ?string
    {
        return self::toOrderApiValue(
            $packageType,
            ApiMapperService::forPackageType(),
            OrderApiPackageType::getAllowableEnumValues()
        );
    }

    /**
     * Convert delivery type to CONSTANT_CASE format for Order Service.
     */
    private static function formatDeliveryType(string $deliveryType): ?string
    {
        $formatted = self::toOrderApiValue(
            $deliveryType,
            ApiMapperService::forDeliveryType(),
            OrderApiDeliveryType::getAllowableEnumValues()
        );

        if (null === $formatted) {
            Logger::warning("Unmapped delivery type: {$deliveryType} - this delivery type will be null in the API response");
        }

        return $formatted;
    }

    /**
     * Return the value when the Order API accepts it, otherwise the V2 name the SDK maps the legacy name to.
     *
     * @param  string                                            $value
     * @param  \MyParcelNL\Sdk\Services\Mapping\ApiMapperService $mapper
     * @param  string[]                                          $allowedValues
     *
     * @return null|string
     */
    private static function toOrderApiValue(string $value, ApiMapperService $mapper, array $allowedValues): ?string
    {
        $v2Name = in_array($value, $allowedValues, true) ? $value : $mapper->v2NameFromLegacyName($value);

        return in_array($v2Name, $allowedValues, true) ? $v2Name : null;
    }
}
