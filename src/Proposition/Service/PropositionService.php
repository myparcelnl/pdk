<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Service;

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierMetadata;
use MyParcelNL\Pdk\Proposition\Model\PropositionConfig;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use RuntimeException;

class PropositionService
{
    public function __construct()
    {
    }

    /**
     * Get the active proposition name.
     * This is used to fetch the proposition config.
     *
     * @return string
     */
    public function getActivePropositionName(): string
    {
        // This should return the active proposition name based on the platform or context.
        // For now, we will return a hardcoded value for testing purposes.
        return 'myparcel'; // @todo This should be replaced with the actual logic to get the active proposition
    }

    /**
     * Get the proposition config.
     *
     * @return \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
     */
    public function getPropositionConfig(): \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
    {
        $propositionName = $this->getActivePropositionName();
        // @todo basic static caching implementation
        // @todo also add DB storage at some point
        return $this->fetchPropositionConfig($propositionName);
    }

    /**
     * Fetch the proposition config based on the platform name.
     * @param string $propositionName
     * @return PropositionConfig
     */
    public function fetchPropositionConfig(string $propositionName): \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
    {
        // $assetsClient = new Client();
        // Fetch the proposition config from a json file
        // open config/propositions/{propositionName}.json

        /**
         * @todo temporary testing code --- IGNORE ---
         * This should be replaced with a proper implementation that fetches the config from assets.
         */
        $filePath = __DIR__ . '/../../../config/proposition/' . $propositionName . '.json';
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('Proposition config file not found: %s', $filePath));
        }
        $configData = file_get_contents($filePath);
        if ($configData === false) {
            throw new \RuntimeException(sprintf('Failed to read proposition config file: %s', $filePath));
        }
        $configArray = json_decode($configData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf('Invalid JSON in proposition config file: %s', $filePath));
        }
        // Create a PropositionConfig instance from the array
        $propositionConfig = new PropositionConfig($configArray);
        return $propositionConfig;

    }

    /**
     * Get the carriers from the proposition config as CarrierCollection.
     * @param $legacyFormat Whether to return the carriers in the legacy format (lowercase) or the new format (SCREAMING_SNAKE_CASE).
     *           Legacy = "postnl", "dhlparcelconnect", "bpost", etc.
     *           New = "POSTNL", "DHL_PARCEL_CONNECT", "BPOST", etc.
     * @return CarrierCollection
     */
    public function getCarriers($legacyFormat = false): \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
    {
        $carrierModels = [];
        foreach ($this->getPropositionConfig()->contracts->available as $contract) {
            $carrierData = [
                'name' => $contract['carrier']['name'],
                'id' => $contract['carrier']['id'],
                'contractId' => $contract['id'] ?? null,
                'outboundFeatures' => $contract['outboundFeatures'] ?? [],
                'inboundFeatures' => $contract['inboundFeatures'] ?? [],
                'features' => $contract['metadata'] ?? [],
            ];
            if ($legacyFormat) {
                $carrierData = $this->convertCarrierDataToLegacyFormat($carrierData);
            }
            $carrierModels[] = new Carrier($carrierData);
        }
        return new CarrierCollection($carrierModels);
    }

    /**
     * Get the default carrier from the proposition config.
     * Returns the outbound carrier by default. Use $outbound = false to get the inbound (return shipments) carrier.

     * @param bool $outbound
     * @return null|Carrier
     */
    public function getDefaultCarrier($outbound = true): Carrier
    {
        if ($outbound) {
            $defaultCarrierId = $this->getPropositionConfig()->contracts->outbound['default']['carrier']['id'];
        } else {
            $defaultCarrierId = $this->getPropositionConfig()->contracts->inbound['default']['carrier']['id'];
        }
        return $this->getCarriers()->where('id', $defaultCarrierId)->first();
    }

    /**
     * Map the proposition config to the platform config for backwards compatibility.
     * This maps to existing Platform config keys, but does not transform the values.
     *
     * @param PropositionConfig $propositionConfig
     * @return array
     * @throws RuntimeException
     */
    public function mapToPlatformConfig(PropositionConfig $propositionConfig): array
    {
        // Map the proposition config to the platform config
        return [
            'name' => $propositionConfig->proposition->key,
            'human' => $propositionConfig->proposition->name,
            'backofficeUrl' => $propositionConfig->applicationUrls['backoffice'] ?? null,
            'supportUrl' => $propositionConfig->applicationUrls['support'] ?? null,
            'localCountry' => $propositionConfig->countryCode,
            'defaultCarrier' => $this->getDefaultCarrier()->name,
            'defaultCarrierId' => $this->getDefaultCarrier()->id,
            'defaultSettings' => [], // @todo no longer supported, remove in v3.0.0
            'carriers' => $this->getCarriers()->toArray(),
        ];
    }

    /**
     * Convert the carrier data to the legacy format.
     * This is used to convert the carrier data to the legacy format for backwards compatibility.
     *
     * @param array $carrierData
     * @return array
     */
    protected function convertCarrierDataToLegacyFormat(array $carrierData): array
    {
        $carrierData['name'] = Carrier::CARRIER_NAME_TO_LEGACY_MAP[$carrierData['name']] ?? $carrierData['name'];

        $carrierData['capabilities']['deliveryTypes'] = \array_map(function (string $value) {
            return DeliveryOptions::DELIVERY_TYPE_NAME_TO_LEGACY_MAP[$value];
        }, $carrierData['outboundFeatures']['deliveryTypes'] ?? []);
        $carrierData['capabilities']['packageTypes'] = \array_map(function (string $value) {
            return DeliveryOptions::PACKAGE_TYPE_NAME_TO_LEGACY_MAP[$value];
        }, $carrierData['outboundFeatures']['packageTypes'] ?? []);
        $carrierData['capabilities']['shipmentOptions'] = \array_reduce(
            $carrierData['outboundFeatures']['shipmentOptions'] ?? [],
            function (array $acc, string $value) {
                $legacyKey = ShipmentOptions::SHIPMENT_OPTION_NAME_TO_LEGACY_MAP[$value] ?? $value;
                $acc[$legacyKey] = true;
                if ($legacyKey === ShipmentOptions::INSURANCE_LEGACY) {
                    $acc[ShipmentOptions::INSURANCE_LEGACY] = [0, 1000, 4000]; // Ensure legacy insurance is also set
                }
                return $acc;
            },
            []
        );
        $carrierData['capabilities']['features'] = $carrierData['outboundFeatures']['metadata'] ?? [];


        $carrierData['returnCapabilities']['deliveryTypes'] = \array_map(function (string $value) {
            return DeliveryOptions::DELIVERY_TYPE_NAME_TO_LEGACY_MAP[$value];
        }, $carrierData['inboundFeatures']['deliveryTypes'] ?? []);
        $carrierData['returnCapabilities']['packageTypes'] = \array_map(function (string $value) {
            return DeliveryOptions::PACKAGE_TYPE_NAME_TO_LEGACY_MAP[$value];
        }, $carrierData['inboundFeatures']['packageTypes'] ?? []);
        $carrierData['returnCapabilities']['shipmentOptions'] = \array_reduce(
            $carrierData['inboundFeatures']['shipmentOptions'] ?? [],
            function (array $acc, string $value) {
                $legacyKey = ShipmentOptions::SHIPMENT_OPTION_NAME_TO_LEGACY_MAP[$value] ?? $value;
                $acc[$legacyKey] = true;

                if ($legacyKey === ShipmentOptions::INSURANCE_LEGACY) {
                    $acc[ShipmentOptions::INSURANCE_LEGACY] = [0, 1000, 4000]; // @todo Ensure legacy insurance is also set
                }

                return $acc;
            },
            []
        );

        $carrierData['returnCapabilities']['features'] = $carrierData['inboundFeatures']['metadata'] ?? [];

        unset($carrierData['outboundFeatures'], $carrierData['inboundFeatures']);

        return $carrierData;
    }

    /**
     * Checks if a carrier has a specific metadata feature, including a check for custom-contract only features.
     * @deprecated should use Carrier capabilities instead.
     * @param  string $feature
     *
     * @return bool
     */
    protected function carrierHasMetadataFeature(Carrier $carrier, string $feature): bool
    {
        $feature = $carrier->outboundFeatures->metadata[$feature] ?? null;
        // Also check legacy capabilities key
        // @todo remove in next major
        if ($feature === null) {
            $feature = $carrier->capabilities->features[$feature] ?? null;
        }

        if (PropositionCarrierMetadata::FEATURE_CUSTOM_CONTRACT_ONLY === $feature) {
            return $carrier->isCustom;
        }

        return (bool) $feature;
    }
}
