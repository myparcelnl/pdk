<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Platform;
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

    public function getAllTranslatable(): array
    {
        $all = $this->getAll();

        return array_combine(
            $all,
            array_map(static fn(string $country) => sprintf('country_%s', Str::lower($country)), $all)
        );
    }

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

    public function isEu(string $country): bool
    {
        return CountryCodes::ZONE_EU === $this->getShippingZone($country);
    }

    public function isLocalCountry(string $country): bool
    {
        return Platform::get('localCountry') === $country;
    }

    public function isRow(string $country): bool
    {
        return CountryCodes::ZONE_ROW === $this->getShippingZone($country);
    }

    public function isUnique(string $country): bool
    {
        return $country === $this->getShippingZone($country);
    }
}
