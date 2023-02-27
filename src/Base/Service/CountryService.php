<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use MyParcelNL\Sdk\src\Support\Str;

class CountryService implements CountryServiceInterface
{
    /**
     * @return string[]
     */
    public function getAll(): array
    {
        return CountryCodes::ALL;
    }

    /**
     * @return array
     */
    public function getAllTranslatable(): array
    {
        $all = $this->getAll();

        return array_combine(
            $all,
            array_map(static function (string $country) {
                return sprintf('country_%s', Str::lower($country));
            }, $all)
        );
    }

    /**
     * @param  string $country
     *
     * @return string
     */
    public function getShippingZone(string $country): string
    {
        if (in_array($country, CountryCodes::UNIQUE_COUNTRIES, true)) {
            return $country;
        }

        if (in_array($country, CountryCodes::EU_COUNTRIES, true)) {
            return CountryCodes::ZONE_EU;
        }

        return CountryCodes::ZONE_ROW;
    }

    /**
     * @param  string $country
     *
     * @return bool
     */
    public function isEu(string $country): bool
    {
        return CountryCodes::ZONE_EU === $this->getShippingZone($country);
    }

    /**
     * @param  string $country
     *
     * @return bool
     */
    public function isRow(string $country): bool
    {
        return CountryCodes::ZONE_ROW === $this->getShippingZone($country);
    }

    /**
     * @param  string $country
     *
     * @return bool
     */
    public function isUnique(string $country): bool
    {
        return $country === $this->getShippingZone($country);
    }
}
