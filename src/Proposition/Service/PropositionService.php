<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Service;

use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Proposition\Model\PropositionConfig;
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
     * @var \MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface
     */
    private $carrierRepository;

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface $carrierRepository
     */
    public function __construct(CarrierRepositoryInterface $carrierRepository)
    {
        $this->carrierRepository = $carrierRepository;
    }

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
     * Whether or not a proposition has been set.
     * @return bool
     */
    public function hasActivePropositionId(): bool
    {
        return self::$activePropositionId !== null;
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
     * @TODO: Refactor to shipping rules API once carrier selection is handled there.
     *
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
        $carrier = $this->carrierRepository->findByLegacyId($defaultCarrierId);

        if (! $carrier) {
            throw new \RuntimeException(sprintf('Default %s carrier with id %d was not found', $outbound ? 'outbound' : 'inbound', $defaultCarrierId));
        }

        return $carrier;
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
}
