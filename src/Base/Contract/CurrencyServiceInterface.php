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
     */
    public function calculateVatTotals(array $prices): array;

    /**
     * Convert euros to cents.
     *
     * @param  int|float|string $amount
     */
    public function convertToCents($amount): int;

    /**
     * Convert cents to euros.
     *
     * @param  int|float|string $amount
     */
    public function convertToEuros($amount): float;

    /**
     * @param  int $amount - amount in cents
     */
    public function format(int $amount): string;
}
