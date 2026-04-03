<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Contract;

use MyParcelNL\Pdk\Base\Contract\ModelRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\RepositoryInterface;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

/**
 * Repository for retrieving Carrier models from account data.
 * Provides type-safe lookups for carriers by V2 name, legacy name, or ID.
 */
interface CarrierRepositoryInterface extends ModelRepositoryInterface, RepositoryInterface
{
    /**
     * Find a carrier by its V2 format name (e.g., "POSTNL", "DHL_FOR_YOU").
     *
     * @param  string $carrierName Carrier name in V2 CONSTANT_CASE format
     *
     * @return null|\MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    public function find($carrierName): ?Carrier;

    /**
     * Find a carrier by its legacy name (e.g., "postnl", "dhlforyou").
     *
     * @param  string $legacyName Carrier name in legacy lowercase format
     *
     * @return null|\MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    public function findByLegacyName(string $legacyName): ?Carrier;

    /**
     * Find a carrier by its numeric ID.
     *
     * @param  int $id Carrier ID
     *
     * @return null|\MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    public function findByLegacyId(int $id): ?Carrier;

    /**
     * Find a carrier by its V2 name or throw an exception if not found.
     *
     * @param  string $carrierName Carrier name in V2 format
     *
     * @return \MyParcelNL\Pdk\Carrier\Model\Carrier
     * @throws \MyParcelNL\Pdk\Base\Exception\ModelNotFoundException
     */
    public function findOrFail($carrierName): Carrier;

    /**
     * Find multiple carriers by their V2 names.
     *
     * @param  string[] $carrierNames Array of carrier names in V2 format
     *
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function findAll(array $carrierNames): CarrierCollection;

    /**
     * Retrieve all available carriers.
     *
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function all(): CarrierCollection;

    /**
     * Check if a carrier exists by its V2 name.
     *
     * @param  string $carrierName Carrier name in V2 format
     *
     * @return bool
     */
    public function exists($carrierName): bool;
}
