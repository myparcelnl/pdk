<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use InvalidArgumentException;
use MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface;
use MyParcelNL\Pdk\Base\Exception\ModelNotFoundException;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

/**
 * Repository for retrieving Carrier models from account data.
 * Provides caching and type-safe lookups for carriers by V2 name, legacy name, or ID.
 */
class CarrierRepository extends Repository implements CarrierRepositoryInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface
     */
    private $accountSettingsService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface               $storage
     * @param  \MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface $accountSettingsService
     */
    public function __construct(StorageInterface $storage, AccountSettingsServiceInterface $accountSettingsService)
    {
        parent::__construct($storage);
        $this->accountSettingsService = $accountSettingsService;
    }

    /**
     * @param  string $carrierName
     *
     * @return null|\MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    public function find($carrierName): ?Carrier
    {
        // return $this->retrieve($this->getCacheKey($carrierName), function () use ($carrierName) {
        return $this->findCarrierInCollection($carrierName);
        // });
    }

    /**
     * @param  string[] $carrierNames
     *
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function findAll(array $carrierNames): CarrierCollection
    {
        $carriers = [];

        foreach ($carrierNames as $carrierName) {
            $carrier = $this->find($carrierName);

            if ($carrier) {
                $carriers[] = $carrier;
            }
        }

        return new CarrierCollection($carriers);
    }

    /**
     * @param  string $legacyName
     *
     * @return null|\MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    public function findByLegacyName(string $legacyName): ?Carrier
    {
        $v2Name = $this->normalizeFromLegacyName($legacyName);

        if (! $v2Name) {
            throw new InvalidArgumentException(sprintf('No mapping found for legacy name %s', $legacyName));
        }

        return $this->find($v2Name);
    }

    /**
     * @param  int $id
     *
     * @return null|\MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    public function findByLegacyId(int $id): ?Carrier
    {
        $v2Name = $this->normalizeFromId($id);

        if (! $v2Name) {
            throw new InvalidArgumentException(sprintf('No mapping found for legacy ID %d', $id));
        }

        return $this->find($v2Name);
    }

    /**
     * @param  string $carrierName
     *
     * @return \MyParcelNL\Pdk\Carrier\Model\Carrier
     * @throws \MyParcelNL\Pdk\Base\Exception\ModelNotFoundException
     */
    public function findOrFail($carrierName): Carrier
    {
        $carrier = $this->find($carrierName);

        if (! $carrier) {
            throw new ModelNotFoundException(Carrier::class, [$carrierName]);
        }

        return $carrier;
    }

    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function all(): CarrierCollection
    {
        return $this->retrieveAll(function () {
            return $this->accountSettingsService->getCarriers();
        });
    }

    /**
     * @param  string $carrierName
     *
     * @return bool
     */
    public function exists($carrierName): bool
    {
        return null !== $this->find($carrierName);
    }

    /**
     * @return string
     */
    protected function getKeyPrefix(): string
    {
        return 'carrier:';
    }

    /**
     * Find a carrier in the collection from AccountSettingsService.
     *
     * @param  string $carrierName V2 format carrier name
     *
     * @return null|\MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    private function findCarrierInCollection(string $carrierName): ?Carrier
    {
        $carriers = $this->accountSettingsService->getCarriers();
        return $carriers->firstWhere('carrier', $carrierName);
    }

    /**
     * Generate a cache key for a carrier.
     *
     * @param  string $carrierName
     *
     * @return string
     */
    private function getCacheKey(string $carrierName): string
    {
        return $carrierName;
    }

    /**
     * Convert a legacy carrier name to V2 format.
     *
     * @param  string $legacyName
     *
     * @return null|string
     */
    private function normalizeFromLegacyName(string $legacyName): ?string
    {
        $legacyToV2Map = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);

        return $legacyToV2Map[$legacyName] ?? null;
    }

    /**
     * Convert a carrier ID to V2 format name.
     *
     * @param  int $id
     *
     * @return null|string
     */
    private function normalizeFromId(int $id): ?string
    {
        return array_search($id, Carrier::CARRIER_NAME_ID_MAP, true) ?: null;
    }
}
