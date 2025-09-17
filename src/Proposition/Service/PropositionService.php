<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Service;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Platform\PlatformManager;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierMetadata;
use MyParcelNL\Pdk\Proposition\Model\PropositionConfig;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Support\Str;
use RuntimeException;

class PropositionService
{
    /**
     * Static cache for proposition configs
     * @var array<string, PropositionConfig>
     */
    private static $configCache = [];

    /**
     * Get the active proposition name.
     * This is used to fetch the proposition config.
     *
     * @return string
     */
    public function getActivePropositionName(): string
    {
        return Pdk::get(PlatformManager::class)->getPlatform();
    }

    /**
     * Get the active proposition config.
     *
     * @return \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
     */
    public function getPropositionConfig(): \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
    {
        $propositionName = $this->getActivePropositionName();
        return $this->getPropositionConfigByName($propositionName);
    }

    /**
     * Get a specific proposition config by name with static caching.
     *
     * @param string $propositionName
     * @return \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
     */
    public function getPropositionConfigByName(string $propositionName): \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
    {
        // Check if config is already cached
        if (isset(self::$configCache[$propositionName])) {
            return self::$configCache[$propositionName];
        }

        // Fetch and cache the config
        Logger::debug('Proposition config loaded from source', ['proposition' => $propositionName]);
        $config = $this->fetchPropositionConfig($propositionName);
        self::$configCache[$propositionName] = $config;

        return $config;
    }

    /**
     * Fetch the proposition config based on the platform name.
     * @param string $propositionName
     * @return PropositionConfig
     */
    public function fetchPropositionConfig(string $propositionName): \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
    {
        $url = null;
        $filePath = null;
        $configData = null;
        switch ($propositionName) {
            case Platform::FLESPAKKET_NAME:
                // @todo: remove flespakket in next major version
                Logger::deprecated('Flespakket platform is deprecated, please use MyParcel or SendMyParcel instead.');
                break;
            default:
                $filePath = __DIR__ . '/../../../config/proposition/' . $propositionName . '.json';
                break;
        }

        if ($filePath) {
            if (!file_exists($filePath)) {
                Logger::error('Proposition config file not found', [
                    'proposition' => $propositionName,
                    'filePath' => $filePath
                ]);
                throw new \InvalidArgumentException(sprintf('Proposition config name %s does not exist', $propositionName));
            }
            $configData = file_get_contents($filePath);
            if ($configData === false) {
                Logger::error('Failed to read proposition config file', [
                    'proposition' => $propositionName,
                    'filePath' => $filePath
                ]);
                throw new \RuntimeException(sprintf('Failed to read proposition config file: %s', $filePath));
            }
        }

        $configArray = json_decode($configData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::error('Invalid JSON in proposition config', [
                'proposition' => $propositionName,
                'filePath' => $filePath,
                'url' => $url,
                'jsonError' => json_last_error_msg()
            ]);
            throw new \RuntimeException(sprintf('Invalid JSON in proposition config file: %s %s - Error: %s', $filePath, $url, json_last_error_msg()));
        }

        // Create a PropositionConfig instance from the array
        $propositionConfig = new PropositionConfig($configArray);
        return $propositionConfig;
    }

    /**
     * Get the carriers from the proposition config as CarrierCollection.
     *
     * @return CarrierCollection
     */
    public function getCarriers(): \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
    {
        $carrierModels = [];
        foreach ($this->getPropositionConfig()->contracts->available as $contract) {
            $carrierData = [
                'name' => $contract['carrier']['name'],
                'id' => $contract['carrier']['id'],
                'outboundFeatures' => $contract['outboundFeatures'] ?? [],
                'inboundFeatures' => $contract['inboundFeatures'] ?? [],
            ];
            $carrierModels[] = new Carrier($carrierData);
        }

        // Combine with carrier-specific own contracts
        foreach ($this->getPropositionConfig()->contracts->availableForCustomCredentials as $customContract) {
            // Skip already-defined carriers
            if (in_array($customContract['carrier']['id'], array_column($carrierModels, 'id'))) {
                continue;
            }
            $carrierData = [
                'name' => $customContract['carrier']['name'],
                'id' => $customContract['carrier']['id'],
                'type' => Carrier::TYPE_CUSTOM
            ];
            $carrierModels[] = new Carrier($carrierData);
        }
        return new CarrierCollection($carrierModels);
    }

    /**
     * Get a specific carrier by its id from the proposition config.

     * @param bool $outbound
     * @return null|Carrier
     */
    public function getCarrierById(int $id): ?Carrier
    {
        return $this->getCarriers()->where('id', $id)->first();
    }

    /**
     * Get a specific carrier by its machine-readable name from the proposition config.

     * @param bool $outbound
     * @return null|Carrier
     */
    public function getCarrierByName(string $name): ?Carrier
    {
        return $this->getCarriers()->where('name', $name)->first();
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
     * Clear the proposition config cache.
     * This is useful for testing or when configs need to be refreshed.
     *
     * @param string|null $propositionName If provided, only clear cache for this specific proposition
     * @return void
     */
    public function clearCache(?string $propositionName = null): void
    {
        if ($propositionName) {
            unset(self::$configCache[$propositionName]);
            Logger::debug('Proposition config cache cleared for specific proposition', ['proposition' => $propositionName]);
        } else {
            self::$configCache = [];
            Logger::debug('Proposition config cache cleared for all propositions');
        }
    }

    /**
     * Check if a proposition config is cached.
     *
     * @param string $propositionName
     * @return bool
     */
    public function isCached(string $propositionName): bool
    {
        return isset(self::$configCache[$propositionName]);
    }

    /**
     * Get the number of cached proposition configs.
     *
     * @return int
     */
    public function getCacheSize(): int
    {
        return count(self::$configCache);
    }

    /**
     * Get cache statistics for monitoring.
     *
     * @return array
     */
    public function getCacheStats(): array
    {
        return [
            'size' => count(self::$configCache),
            'cached_propositions' => array_keys(self::$configCache),
            'memory_usage' => memory_get_usage(true),
        ];
    }

    /**
     * Map new carrier name (SCREAMING_SNAKE_CASE) to legacy name (lowercase).
     * This is used for backwards compatibility with existing settings.
     *
     * @param string $newCarrierName
     * @return string
     */
    public function mapNewToLegacyCarrierName(string $newCarrierName): string
    {
        $mapping = Carrier::CARRIER_NAME_TO_LEGACY_MAP;
        return $mapping[$newCarrierName] ?? strtolower($newCarrierName);
    }

    /**
     * Map legacy carrier name (lowercase) to new name (SCREAMING_SNAKE_CASE).
     * This is used for forwards compatibility when reading settings.
     *
     * @param string $legacyCarrierName
     * @return string
     */
    public function mapLegacyToNewCarrierName(string $legacyCarrierName): string
    {
        $mapping = \array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);
        return $mapping[$legacyCarrierName] ?? strtoupper($legacyCarrierName);
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
        return [
            'name' => $propositionConfig->proposition->key,
            'human' => $propositionConfig->proposition->name,
            'backofficeUrl' => $propositionConfig->applications['backoffice']['url'] ?? null,
            'supportUrl' => $propositionConfig->applications['developerPortal']['url'] ?? null,
            'localCountry' => $propositionConfig->countryCode,
            'defaultCarrier' => $this->mapNewToLegacyCarrierName($this->getDefaultCarrier()->name),
            'defaultCarrierId' => $this->getDefaultCarrier()->id,
            'defaultSettings' => [], // @todo no longer supported, remove in v3.0.0
            'carriers' => FrontendData::carrierCollectionToLegacyFormat($this->getCarriers())->toArray(),
        ];
    }

    /**
     * Given SCREAMING_SNAKE_CASE package name, return the snake_case version for delivery options if defined in that class.
     *
     * @param string $packageType a package type definition from the Proposition config
     * @return string|false a package type definition suitable for Shipments (delivery options) or false if not supported currently
     */
    public function packageTypeNameForDeliveryOptions(string $packageType)
    {
        $supportedTypes = DeliveryOptions::PACKAGE_TYPES_NAMES;
        // Specific conversion for SMALL_PACKAGE to package_small
        if ($packageType === PropositionCarrierFeatures::PACKAGE_TYPE_PACKAGE_SMALL_NAME) {
            return DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME;
        }
        $converted = strtolower($packageType);

        return in_array($converted, $supportedTypes, true) ? $converted : false;
    }

    /**
     * Given SCREAMING_SNAKE_CASE delivery type name, return the snake_case version for delivery options if defined in that class.
     *
     * @param string $deliveryType a delivery type definition from the Proposition config
     * @return string|false a delivery type definition suitable for Shipments (delivery options) or false if not supported currently
     */
    public function deliveryTypeNameForDeliveryOptions(string $deliveryType)
    {
        $supportedTypes = DeliveryOptions::DELIVERY_TYPES_NAMES;
        $converted = strtolower(str_replace('_DELIVERY', '', $deliveryType));

        return in_array($converted, $supportedTypes, true) ? $converted : false;
    }

    /**
     * Given SCREAMING_SNAKE_CASE shipment option, return the camelCase version for delivery options if defined in that class.
     *
     * @param string $shipmentOption a shipment option definition from the Proposition config
     * @return string a shipment option definition suitable for Shipments (delivery options) or false if not supported currently
     */
    public function shipmentOptionNameForDeliveryOptions(string $shipmentOption)
    {
        return Str::camel(strtolower($shipmentOption));
    }
}
