<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Facade\Language;

class CurrencyService implements CurrencyServiceInterface
{
    /**
     * @param  array{price?: int, vat?: int, priceAfterVat?: int} $prices
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function calculateVatTotals(array $prices): array
    {
        $price         = $prices['price'] ?? 0;
        $vat           = $prices['vat'] ?? 0;
        $priceAfterVat = $prices['priceAfterVat'] ?? 0;

        if ($price && $vat) {
            $priceAfterVat = $price + $vat;
        } elseif ($price && $priceAfterVat) {
            $vat = $priceAfterVat - $price;
        } elseif ($priceAfterVat && $vat) {
            $price = $priceAfterVat - $vat;
        } elseif ($price && ! $priceAfterVat && ! $vat) {
            $priceAfterVat = $price;
        } elseif ($priceAfterVat && ! $price && ! $vat) {
            $price = $priceAfterVat;
        } elseif ($vat) {
            $priceAfterVat = $vat;
        }

        return [
            'price'         => $price,
            'vat'           => $vat,
            'priceAfterVat' => $priceAfterVat,
        ];
    }

    /**
     * @param  int|float|string $amount
     *
     * @return int
     */
    public function convertToCents($amount): int
    {
        return (int) round((float) $amount * 100);
    }

    /**
     * @param  int|float|string $amount
     *
     * @return float
     */
    public function convertToEuro($amount): float
    {
        return (float) $amount / 100;
    }

    /**
     * @param  int $amount
     *
     * @return string
     */
    public function format(int $amount): string
    {
        $currencySymbol = '€';
        $iso2           = Language::getIso2();

        if ('nl' === $iso2) {
            return sprintf('%s %s', $currencySymbol, number_format($amount / 100, 2, ',', '.'));
        }

        return $currencySymbol . number_format($amount / 100, 2);
    }
}
