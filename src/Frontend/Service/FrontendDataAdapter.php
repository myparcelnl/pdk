<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Contract\FrontendDataAdapterInterface;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * FrontendDataAdapter converts new proposition configuration data to the old format
 * that JS-PDK and Delivery Options expect. This ensures backwards compatibility
 * while the backend uses the new proposition configuration.
 */
class FrontendDataAdapter implements FrontendDataAdapterInterface
{
    /**
     * @var \MyParcelNL\Pdk\Proposition\Service\PropositionService
     */
    private $propositionService;

    public function __construct(PropositionService $propositionService)
    {
        $this->propositionService = $propositionService;
    }

    /**
     * Get carriers in the old format that JS-PDK and Delivery Options expect.
     * This converts the new proposition config carriers to the old carrier structure.
     *
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function carrierCollectionToLegacyFormat(CarrierCollection $carrierCollection): CarrierCollection
    {
        $legacyCarriers = new CarrierCollection();

        foreach ($carrierCollection as $carrier) {
            $legacyCarrier = $this->convertCarrierToLegacyFormat($carrier);
            $legacyCarriers->push($legacyCarrier);
        }

        return $legacyCarriers;
    }

    public function getLegacyIdentifier(string $externalIdentifier): string
    {
        $parts = explode(':', $externalIdentifier);
        // Convert only the carrier part, keep the rest as is.
        if (count($parts) > 1) {
            return $this->propositionService->mapNewToLegacyCarrierName($parts[0]) . ':' . implode(':', array_slice($parts, 1));
        }
        return $this->propositionService->mapNewToLegacyCarrierName($parts[0]);
    }

    /**
     * Convert a new proposition carrier to the old legacy format.
     *
     * @param Carrier $carrier
     * @return Carrier
     */
    public function convertCarrierToLegacyFormat(Carrier $carrier): Carrier
    {
        // Retain new carrier attributes, only override what is needed for legacy support.
        $legacyData = $carrier->getAttributes();
        $legacyData['name'] = $this->propositionService->mapNewToLegacyCarrierName($carrier->name);

        // Add legacy capabilities structure
        $legacyData['capabilities'] = $this->convertCapabilitiesToLegacyFormat($carrier->outboundFeatures);

        // Add return capabilities
        $legacyData['returnCapabilities'] = $this->convertCapabilitiesToLegacyFormat($carrier->inboundFeatures);

        return new Carrier($legacyData);
    }

    /**
     * Convert new proposition capabilities to the old legacy format.
     *
     * @param mixed $features
     * @return array
     */
    private function convertCapabilitiesToLegacyFormat($features): array
    {
        if (!$features) {
            return [];
        }

        $legacyCapabilities = [];

        // Convert package types
        if (isset($features->packageTypes)) {
            $legacyCapabilities['packageTypes'] = array_map(function ($packageType) {
                return $this->propositionService->packageTypeNameForDeliveryOptions($packageType) ?? strtolower($packageType);
            }, $features->packageTypes);
        }

        // Convert delivery types
        if (isset($features->deliveryTypes)) {
            $legacyCapabilities['deliveryTypes'] = array_map(function ($deliveryType) {
                return $this->propositionService->deliveryTypeNameForDeliveryOptions($deliveryType) ?? strtolower($deliveryType);
            }, $features->deliveryTypes);
        }

        // Convert shipment options - these should be boolean values or arrays for insurance
        if (isset($features->shipmentOptions)) {
            $legacyCapabilities['shipmentOptions'] = [];
            foreach ($features->shipmentOptions as $option) {
                $legacyOptionName = $this->propositionService->shipmentOptionNameForDeliveryOptions($option);
                if ($legacyOptionName !== 'insurance') {
                    $legacyCapabilities['shipmentOptions'][$legacyOptionName] = true;
                }
            }
        }

        // Convert metadata to features
        if (isset($features['metadata'])) {
            $legacyCapabilities = \array_merge_recursive($legacyCapabilities, $this->convertMetadataToLegacyFormat($features['metadata']));
        }

        // print_r($legacyCapabilities['shipmentOptions']);

        return $legacyCapabilities;
    }

    /**
     * Convert new proposition metadata to the old legacy format.
     *
     * @param array $metadata
     * @return array
     */
    private function convertMetadataToLegacyFormat(array $metadata): array
    {
        if (!$metadata) {
            return [];
        }

        $mapped = [];

        // Map common metadata fields
        if (isset($metadata['labelDescriptionLength'])) {
            $mapped['features']['labelDescriptionLength'] = $metadata['labelDescriptionLength'];
        }

        if (isset($metadata['carrierSmallPackageContract'])) {
            $mapped['features']['carrierSmallPackageContract'] = $metadata['carrierSmallPackageContract'];
        }

        if (isset($metadata['multiCollo'])) {
            $mapped['features']['multiCollo'] = $metadata['multiCollo'];
        }

        if (isset($metadata['insuranceOptions'])) {
            $mapped['shipmentOptions']['insurance'] = $metadata['insuranceOptions'];
        }

        return $mapped;
    }

    /**
     * Get package types in the old format.
     *
     * @return array
     */
    public function getLegacyPackageTypes(): array
    {
        $proposition = $this->propositionService->getPropositionConfig();
        $packageTypes = [];

        foreach ($proposition->contracts->available as $contract) {
            if ($contract->outboundFeatures && isset($contract->outboundFeatures->packageTypes)) {
                foreach ($contract->outboundFeatures->packageTypes as $packageType) {
                    $legacyPackageType = $this->propositionService->packageTypeNameForDeliveryOptions($packageType);
                    if ($legacyPackageType && !in_array($legacyPackageType, $packageTypes)) {
                        $packageTypes[] = $legacyPackageType;
                    }
                }
            }
        }

        return $packageTypes;
    }

    /**
     * Get delivery types in the old format.
     *
     * @return array
     */
    public function getLegacyDeliveryTypes(): array
    {
        $proposition = $this->propositionService->getPropositionConfig();
        $deliveryTypes = [];

        foreach ($proposition->contracts->available as $contract) {
            if ($contract->outboundFeatures && isset($contract->outboundFeatures->deliveryTypes)) {
                foreach ($contract->outboundFeatures->deliveryTypes as $deliveryType) {
                    $legacyDeliveryType = $this->propositionService->deliveryTypeNameForDeliveryOptions($deliveryType);
                    if ($legacyDeliveryType && !in_array($legacyDeliveryType, $deliveryTypes)) {
                        $deliveryTypes[] = $legacyDeliveryType;
                    }
                }
            }
        }

        return $deliveryTypes;
    }

    /**
     * Get shipment options in the old format.
     *
     * @return array
     */
    public function getLegacyShipmentOptions(): array
    {
        $proposition = $this->propositionService->getPropositionConfig();
        $shipmentOptions = [];

        foreach ($proposition->contracts->available as $contract) {
            if ($contract->outboundFeatures && isset($contract->outboundFeatures->shipmentOptions)) {
                foreach ($contract->outboundFeatures->shipmentOptions as $option) {
                    $legacyOption = $this->propositionService->shipmentOptionNameForDeliveryOptions($option);
                    if ($legacyOption && !in_array($legacyOption, $shipmentOptions)) {
                        $shipmentOptions[] = $legacyOption;
                    }
                }
            }
        }

        return $shipmentOptions;
    }
}
