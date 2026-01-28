<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Resource;

use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedResource;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * API v1 response formatter for delivery options data.
 *
 * Formats order delivery options according to v1 API specifications.
 */
class DeliveryOptionsV1Resource extends AbstractVersionedResource
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
    public static function format($data): array
    {
        $deliveryOptions = $data['deliveryOptions'] ?? null;
        $orderId = $data['orderId'] ?? null;

        return [
            'orderId' => $orderId,
            'deliveryOptions' => static::formatDeliveryOptions($deliveryOptions),
        ];
    }

    /**
     * Format delivery options for API v1 response.
     */
    private static function formatDeliveryOptions(?DeliveryOptions $deliveryOptions): array
    {
        if (! $deliveryOptions) {
            return [
                'carrier' => null,
                'packageType' => null,
                'deliveryType' => null,
                'shipmentOptions' => [],
                'date' => null,
                'time' => null,
                'pickupLocation' => null,
            ];
        }

        return [
            'carrier' => self::formatCarrier($deliveryOptions->getCarrier() ? $deliveryOptions->getCarrier()->getName() : null),
            'packageType' => self::formatPackageType($deliveryOptions->getPackageType()),
            'deliveryType' => self::formatDeliveryType($deliveryOptions->getDeliveryType()),
            'shipmentOptions' => self::formatShipmentOptions($deliveryOptions),
            'date' => $deliveryOptions->getDate() ? $deliveryOptions->getDate()->format('Y-m-d') : null,
            'time' => self::formatTimeSlot($deliveryOptions),
            'pickupLocation' => self::formatPickupLocation($deliveryOptions),
        ];
    }

    /**
     * Format shipment options following API standards.
     * Returns an array of enabled shipment option names in CONSTANT_CASE format.
     */
    private static function formatShipmentOptions(DeliveryOptions $deliveryOptions): array
    {
        $options = [];
        $shipmentOptions = $deliveryOptions->getShipmentOptions();

        if (! $shipmentOptions) {
            return $options;
        }

        // Add enabled options to array using CONSTANT_CASE names
        if (TriStateService::ENABLED === $shipmentOptions->getSignature()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_SIGNATURE_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getOnlyRecipient()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_ONLY_RECIPIENT_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getReturn()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_DIRECT_RETURN_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getLargeFormat()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_LARGE_FORMAT_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getAgeCheck()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_AGE_CHECK_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getHideSender()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_HIDE_SENDER_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getReceiptCode()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_RECEIPT_CODE_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getSameDayDelivery()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_SAME_DAY_DELIVERY_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getSaturdayDelivery()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_SATURDAY_DELIVERY_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getMondayDelivery()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_MONDAY_DELIVERY_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getTracked()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_TRACKED_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getCollect()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_COLLECT_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getFreshFood()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_FRESH_FOOD_NAME;
        }

        if (TriStateService::ENABLED === $shipmentOptions->getFrozen()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_FROZEN_NAME;
        }

        if ($shipmentOptions->getInsurance() > 0) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_INSURANCE_NAME;
        }

        if ($shipmentOptions->getLabelDescription()) {
            $options[] = PropositionCarrierFeatures::SHIPMENT_OPTION_LABEL_DESCRIPTION_NAME;
        }

        return $options;
    }

    /**
     * Format time slot information.
     */
    private static function formatTimeSlot(DeliveryOptions $deliveryOptions): ?array
    {
        $timeSlot = $deliveryOptions->getTimeSlot();

        if (! $timeSlot) {
            return null;
        }

        return [
            'start' => $timeSlot->getStart() ? $timeSlot->getStart()->format('H:i') : null,
            'end' => $timeSlot->getEnd() ? $timeSlot->getEnd()->format('H:i') : null,
        ];
    }

    /**
     * Format pickup location information.
     */
    private static function formatPickupLocation(DeliveryOptions $deliveryOptions): ?array
    {
        $pickupLocation = $deliveryOptions->getPickupLocation();

        if (! $pickupLocation) {
            return null;
        }

        return [
            'locationCode' => $pickupLocation->getLocationCode(),
            'locationName' => $pickupLocation->getLocationName(),
            'retailNetworkId' => $pickupLocation->getRetailNetworkId(),
            'address' => [
                'street' => $pickupLocation->getStreet(),
                'number' => $pickupLocation->getNumber(),
                'numberSuffix' => $pickupLocation->getNumberSuffix(),
                'postalCode' => $pickupLocation->getPostalCode(),
                'city' => $pickupLocation->getCity(),
                'cc' => $pickupLocation->getCc(),
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
