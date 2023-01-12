<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

class CurrencyService
{
    /**
     * Normalizes an array of any combination of price, vat or priceAfterVat, provided that at least two of the three
     * are present.
     *
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
        $float = (float) $amount;
        return (int) round($float * 100);
    }
}
