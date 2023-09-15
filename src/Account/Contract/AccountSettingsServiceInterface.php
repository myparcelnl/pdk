<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Contract;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;

interface AccountSettingsServiceInterface
{
    public function getAccount(): ?Account;

    /**
     * @deprecated use getCarriers()
     */
    public function getCarrierOptions(): CarrierCollection;

    public function getCarriers(): CarrierCollection;

    public function getShop(): ?Shop;

    public function hasAccount(): bool;

    /**
     * @noinspection PhpUnused
     */
    public function hasTaxFields(): bool;

    public function usesOrderMode(): bool;
}
