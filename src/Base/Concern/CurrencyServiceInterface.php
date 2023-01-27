<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

interface CurrencyServiceInterface
{
    /**
     * Normalizes an array of any combination of price, vat or priceAfterVat, provided that at least two of the three
     * are present.
     *
     * @param  array{price?: int, vat?: int, priceAfterVat?: int} $prices
     *
     * @return array
     */
    public function calculateVatTotals(array $prices): array;

    /**
     * Convert euros to cents.
     *
     * @param  int|float|string $amount
     *
     * @return int
     */
    public function convertToCents($amount): int;
}
