<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Resource;

use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedResource;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
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
            'packageType' => self::formatPackageType($this->model->packageType),
            'deliveryType' => self::formatDeliveryType($this->model->deliveryType),
            'shipmentOptions' => self::formatShipmentOptions($this->model),
            // format date as ISO 8601 string or null
            'date' => $this->model->date ? $this->model->date->format('c'): null,
            'pickupLocation' => self::formatPickupLocation($this->model),
        ];
    }

    /**
     * Format shipment options following API standards.
     * Returns an array of enabled shipment option names in CONSTANT_CASE format.
     */
    private static function formatShipmentOptions(DeliveryOptions $deliveryOptions): array
    {
        $options = [];

        // Create a temporary order to resolve inherited shipment options
        // This uses carrier settings, product settings, and proposition config
        $tempOrder = new PdkOrder(['deliveryOptions' => $deliveryOptions]);

        /** @var PdkOrderOptionsServiceInterface $orderOptionsService */
        $orderOptionsService = Pdk::get(PdkOrderOptionsServiceInterface::class);
        $resolvedOrder = $orderOptionsService->calculateShipmentOptions($tempOrder);

        $shipmentOptions = $resolvedOrder->deliveryOptions->shipmentOptions;

        return array_map(
            fn($key) => Str::upper(Str::snake($key)), // convert key to CONSTANT_CASE
            array_keys(array_filter($shipmentOptions->toArray())) // add only the keys and filter for truthy values
        );
    }

    /**
     * Format pickup location information.
     */
    private static function formatPickupLocation(DeliveryOptions $deliveryOptions): ?array
    {
        $pickupLocation = $deliveryOptions->pickupLocation;

        if (! $pickupLocation) {
            return null;
        }

        return [
            'locationCode' => $pickupLocation->locationCode,
            'locationName' => $pickupLocation->locationName,
            'retailNetworkId' => $pickupLocation->retailNetworkId,
            'address' => [
                'street' => $pickupLocation->street,
                'number' => $pickupLocation->number,
                'numberSuffix' => $pickupLocation->numberSuffix,
                'postalCode' => $pickupLocation->postalCode,
                'city' => $pickupLocation->city,
                'cc' => $pickupLocation->cc,
            ],
        ];
    }

    /**
     * Convert carrier name to CONSTANT_CASE format using PropositionService.
     */
    private static function formatCarrier(?string $carrierName): ?string
    {
        if (!$carrierName) {
            return null;
        }

        $propositionService = Pdk::get(PropositionService::class);
        return $propositionService->mapLegacyToNewCarrierName($carrierName);
    }

    /**
     * Convert package type to CONSTANT_CASE format using existing constants.
     */
    private static function formatPackageType(?string $packageType): ?string
    {
        if (!$packageType) {
            return null;
        }

        // Mapping from DeliveryOptions constants to PropositionCarrierFeatures constants
        $packageTypeMapping = [
            DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME => PropositionCarrierFeatures::PACKAGE_TYPE_PACKAGE_NAME,
            DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME => PropositionCarrierFeatures::PACKAGE_TYPE_MAILBOX_NAME,
            DeliveryOptions::PACKAGE_TYPE_LETTER_NAME => PropositionCarrierFeatures::PACKAGE_TYPE_LETTER_NAME,
            DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME => PropositionCarrierFeatures::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
            DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME => PropositionCarrierFeatures::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
        ];

        return $packageTypeMapping[$packageType] ?? strtoupper($packageType);
    }

    /**
     * Convert delivery type to CONSTANT_CASE format using existing constants.
     */
    private static function formatDeliveryType(?string $deliveryType): ?string
    {
        if (!$deliveryType) {
            return null;
        }

        // Mapping from DeliveryOptions constants to PropositionCarrierFeatures constants
        $deliveryTypeMapping = [
            DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME => PropositionCarrierFeatures::DELIVERY_TYPE_STANDARD_NAME,
            DeliveryOptions::DELIVERY_TYPE_MORNING_NAME => PropositionCarrierFeatures::DELIVERY_TYPE_MORNING_NAME,
            DeliveryOptions::DELIVERY_TYPE_EVENING_NAME => PropositionCarrierFeatures::DELIVERY_TYPE_EVENING_NAME,
            DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME => PropositionCarrierFeatures::DELIVERY_TYPE_PICKUP_NAME,
            DeliveryOptions::DELIVERY_TYPE_EXPRESS_NAME => PropositionCarrierFeatures::DELIVERY_TYPE_EXPRESS_NAME,
        ];

        return $deliveryTypeMapping[$deliveryType] ?? strtoupper($deliveryType);
    }
}
