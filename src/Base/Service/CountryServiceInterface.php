<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

interface CountryServiceInterface
{
    /**
     * Return all countries as a simple array.
     */
    public function getAll(): array;

    /**
     * Return all countries as an associative array with the country code as key and the country translation as value.
     */
    public function getAllTranslatable(): array;

    /**
     * Get shipping zone for a country.
     */
    public function getShippingZone(string $country): string;

    /**
     * Check if a country is in the EU.
     */
    public function isEu(string $country): bool;

    /**
     * Check if a country is a rest of world country.
     */
    public function isRow(string $country): bool;

    /**
     * Check if a country has its own shipping zone.
     */
    public function isUnique(string $country): bool;
}
