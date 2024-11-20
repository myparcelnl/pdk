<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\CurrencyFactory;
use function MyParcelNL\Pdk\Tests\factory;

trait UsesCurrency
{
    /**
     * @param  int|array|Currency|CurrencyFactory $input
     *
     * @return \MyParcelNL\Pdk\Base\Model\CurrencyFactory
     */
    protected function toCurrency($input): CurrencyFactory
    {
        if (is_scalar($input)) {
            return factory(Currency::class)->withAmount($input);
        }

        if (is_array($input)) {
            return factory(Currency::class)->with($input);
        }

        if ($input instanceof Currency) {
            return factory(Currency::class)->with($input->toArray());
        }

        return $input;
    }

    /**
     * @param  int|array|Currency|CurrencyFactory $price
     *
     * @return int
     */
    protected function toInt($price): int
    {
        if (is_array($price)) {
            return $price['amount'] ?? 0;
        }

        if ($price instanceof Currency) {
            return $price->getAmount();
        }

        if ($price instanceof CurrencyFactory) {
            return $price->make()
                ->getAmount();
        }

        return $price ?? 0;
    }

    /**
     * @param  string                             $field
     * @param  int|array|Currency|CurrencyFactory $input
     *
     * @return $this
     */
    protected function withCurrencyField(string $field, $input): self
    {
        return $this->with([$field => $this->toCurrency($input)]);
    }
}
