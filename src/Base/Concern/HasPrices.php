<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;

trait HasPrices
{
    /**
     * @param  array $fields
     *
     * @return void
     */
    protected function calculateVatTotals(array $fields = ['price', 'vat', 'priceAfterVat']): void
    {
        [$priceField, $vatField, $priceAfterVatField] = $fields;

        /** @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface $service */
        $service = Pdk::get(CurrencyServiceInterface::class);

        $totals = $service->calculateVatTotals([
            'price'         => $this->getAttribute($priceField),
            'vat'           => $this->getAttribute($vatField),
            'priceAfterVat' => $this->getAttribute($priceAfterVatField),
        ]);

        $this->attributes[$priceField]         = $totals['price'];
        $this->attributes[$vatField]           = $totals['vat'];
        $this->attributes[$priceAfterVatField] = $totals['priceAfterVat'];
    }

    /**
     * @param  int $priceAfterVat
     *
     * @return self
     */
    protected function setPriceAfterVatAttribute(int $priceAfterVat): self
    {
        $this->attributes['priceAfterVat'] = $priceAfterVat;
        $this->calculateVatTotals();

        return $this;
    }

    /**
     * @param  int $price
     *
     * @return self
     */
    protected function setPriceAttribute(int $price): self
    {
        $this->attributes['price'] = $price;
        $this->calculateVatTotals();

        return $this;
    }

    /**
     * @param  int $vat
     *
     * @return self
     */
    protected function setVatAttribute(int $vat): self
    {
        $this->attributes['vat'] = $vat;
        $this->calculateVatTotals();

        return $this;
    }
}
