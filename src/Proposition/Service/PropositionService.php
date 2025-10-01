<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Service;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Proposition\Model\PropositionConfig;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Support\Str;
use RuntimeException;

class PropositionService
{
    public const FALLBACK_PROPOSITION_ID = 1; // Default proposition ID if none is set

    /**
     * Active proposition ID.
     * @var int|null
     */
    private static $activePropositionId = null;

    /**
     * Static cache for proposition configs
     * @var array<int, PropositionConfig>
     */
    private static $configCache = [];

    /**
     * Get the active proposition ID.
     * This is used to fetch the proposition config.
     *
     * @return int
     */
    public function getActivePropositionId(): int
    {
        if (self::$activePropositionId !== null) {
            return self::$activePropositionId;
        }

        $account = AccountSettings::getAccount();
        if ($account && $account->getAttribute('platformId')) {
            $this->setActivePropositionId($account->getAttribute('platformId'));
            if (self::$activePropositionId !== null) {
                return self::$activePropositionId;
            }
        }
        // @TODO: Defaults to a fallback ID, this should be refactored in the future so that parts of the PDK can work without an active proposition set.
        return static::FALLBACK_PROPOSITION_ID;
    }

    /**
    * Set the active proposition ID.
    * This overrides the ID fetched from the account and is useful for testing.
     * @param int $propositionId
     * @return void
     */
    public function setActivePropositionId(int $propositionId): void
    {
        self::$activePropositionId = $propositionId;
    }

    /**
     * Reset the active proposition ID.
     * This is useful for testing or when the active proposition changes.
     *
     * @return void
     */
    public function clearActivePropositionId(): void
    {
        self::$activePropositionId = null;
    }

    /**
     * Get the active proposition config.
     *
     * @return \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
     */
    public function getPropositionConfig(): \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
    {
        $propositionId = $this->getActivePropositionId();
        return $this->getPropositionConfigById($propositionId);
    }

    /**
     * Get a specific proposition config by ID with static caching.
     *
     * @param int $propositionId
     * @return \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
     */
    public function getPropositionConfigById(int $propositionId): \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
    {
        // Check if config is already cached
        if (isset(self::$configCache[$propositionId])) {
            return self::$configCache[$propositionId];
        }

        // Fetch and cache the config
        Logger::debug('Proposition config loaded from source.', ['proposition' => $propositionId]);
        $config = $this->fetchPropositionConfig($propositionId);
        self::$configCache[$propositionId] = $config;

        return $config;
    }

    /**
     * Fetch the proposition config based on the platform/proposition id.
     * @param int $propositionId
     * @return PropositionConfig
     */
    public function fetchPropositionConfig(int $propositionId): \MyParcelNL\Pdk\Proposition\Model\PropositionConfig
    {
        $filePath = null;
        $configData = null;
        // Emulate an eventual API call that gets the proposition by ID
        $filePath = __DIR__ . '/../../../config/proposition/proposition-' . $propositionId . '.json';

        if (!file_exists($filePath)) {
            Logger::error('Proposition config file not found', [
                'proposition' => $propositionId,
                'filePath' => $filePath
            ]);
            throw new \InvalidArgumentException(sprintf('Proposition config ID %d does not exist', $propositionId));
        }
        $configData = file_get_contents($filePath);

        return $this->processConfigData($propositionId, $filePath, $configData);
    }

    public function processConfigData(int $propositionId, string $filePath, ?string $jsonData): PropositionConfig
    {
        if (!$jsonData) {
            Logger::error('Failed to read proposition config file', [
                'proposition' => $propositionId,
                'filePath' => $filePath
            ]);
            throw new \RuntimeException(sprintf('Proposition config file: %s appears to be empty', $filePath));
        }

        try {
            $configArray = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Logger::error('Invalid JSON in proposition config', [
                'proposition' => $propositionId,
                'filePath' => $filePath,
                'jsonError' => $e->getMessage()
            ]);
            throw new \RuntimeException(sprintf('Invalid JSON in proposition config file: %s - Error: %s', $filePath, $e->getMessage()));
        }

        // Create a PropositionConfig instance from the array
        return new PropositionConfig($configArray);
    }

    /**
     * Get the carriers from the proposition config as CarrierCollection.
     *
     * @return CarrierCollection
     */
    public function getCarriers($supportedDeliveryTypesOnly = false): \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
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

        // Filter out carriers without supported delivery types if requested by checking with packageTypeNameForDeliveryOptions
        if ($supportedDeliveryTypesOnly) {
            $carrierModels = array_values(array_filter($carrierModels, function (Carrier $carrier) {
                $features = $carrier->outboundFeatures;

                if (!$features || !$features->packageTypes) {
                    return false;
                }

                foreach ($features->packageTypes as $packageType) {
                    if ($this->packageTypeNameForDeliveryOptions($packageType)) {
                        return true;
                    }
                }
                return false;
            }));
        }

        return new CarrierCollection($carrierModels);
    }

    /**
     * Get a specific carrier by its id from the proposition config.

     * @param int $id
     * @return null|Carrier
     */
    public function getCarrierById(int $id): ?Carrier
    {
        return $this->getCarriers()->where('id', $id)->first();
    }

    /**
     * Get a specific carrier by its machine-readable name from the proposition config.

     * @param string $name
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
     * @return Carrier
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
     * @param int|null $propositionId If provided, only clear cache for this specific proposition
     * @return void
     */
    public function clearCache(?int $propositionId = null): void
    {
        if ($propositionId) {
            unset(self::$configCache[$propositionId]);
            Logger::debug('Proposition config cache cleared for specific proposition', ['proposition' => $propositionId]);
        } else {
            self::$configCache = [];
            Logger::debug('Proposition config cache cleared for all propositions');
        }
    }

    /**
     * Check if a proposition config is cached.
     *
     * @param int $propositionId
     * @return bool
     */
    public function isCached(int $propositionId): bool
    {
        return isset(self::$configCache[$propositionId]);
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
            'name' => Platform::PLATFORMS_TO_LEGACY_MAP[$propositionConfig->proposition->key] ?? $propositionConfig->proposition->key,
            'human' => $propositionConfig->proposition->name,
            'backofficeUrl' => $propositionConfig->applications['backoffice']['url'] ?? null,
            'supportUrl' => $propositionConfig->applications['developerPortal']['url'] ?? null,
            'localCountry' => $propositionConfig->countryCode,
            'defaultCarrier' => $this->mapNewToLegacyCarrierName($this->getDefaultCarrier()->name),
            'defaultCarrierId' => $this->getDefaultCarrier()->id,
            'carriers' => FrontendData::carrierCollectionToLegacyFormat($this->getCarriers())->toArray(),
        ];
    }

    /**
     * Given SCREAMING_SNAKE_CASE package name, return the snake_case version for delivery options if defined in that class.
     *
     * @param string $packageType a package type definition from the Proposition config
     * @return string|null a package type definition suitable for Shipments (delivery options) or null if not supported currently
     */
    public function packageTypeNameForDeliveryOptions(string $packageType): ?string
    {
        $supportedTypes = DeliveryOptions::PACKAGE_TYPES_NAMES;
        // Specific conversion for SMALL_PACKAGE to package_small
        if ($packageType === PropositionCarrierFeatures::PACKAGE_TYPE_PACKAGE_SMALL_NAME) {
            return DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME;
        }
        $converted = strtolower($packageType);

        return in_array($converted, $supportedTypes, true) ? $converted : null;
    }

    /**
     * Given SCREAMING_SNAKE_CASE delivery type name, return the snake_case version for delivery options if defined in that class.
     *
     * @param string $deliveryType a delivery type definition from the Proposition config
     * @return string|null a delivery type definition suitable for Shipments (delivery options) or null if not supported currently
     */
    public function deliveryTypeNameForDeliveryOptions(string $deliveryType): ?string
    {
        $supportedTypes = DeliveryOptions::DELIVERY_TYPES_NAMES;
        $converted = strtolower(str_replace('_DELIVERY', '', $deliveryType));

        return in_array($converted, $supportedTypes, true) ? $converted : null;
    }

    /**
     * Given SCREAMING_SNAKE_CASE shipment option, return the camelCase version for delivery options if defined in that class.
     *
     * @param string $shipmentOption a shipment option definition from the Proposition config
     * @return string a shipment option definition suitable for Shipments (delivery options) or false if not supported currently
     */
    public function shipmentOptionNameForDeliveryOptions(string $shipmentOption): string
    {
        return Str::camel(strtolower($shipmentOption));
    }
}
