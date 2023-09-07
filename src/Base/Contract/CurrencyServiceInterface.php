<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

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

    /**
     * Convert cents to euros.
     *
     * @param  int|float|string $amount
     *
     * @return float
     */
    public function convertToEuros($amount): float;

    /**
     * @param  int $amount - amount in cents
     *
     * @return string
     */
    public function format(int $amount): string;
}
