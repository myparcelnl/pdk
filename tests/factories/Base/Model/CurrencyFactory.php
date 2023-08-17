<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of Currency
 * @method Currency make()
 * @method $this withAmount(int $amount)
 * @method $this withCurrency(string $currency)
 */
final class CurrencyFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return Currency::class;
    }
}
