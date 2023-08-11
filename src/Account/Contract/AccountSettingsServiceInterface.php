<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Contract;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;

interface AccountSettingsServiceInterface
{
    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    public function getAccount(): ?Account;

    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     * @deprecated use getCarriers()
     */
    public function getCarrierOptions(): CarrierCollection;

    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function getCarriers(): CarrierCollection;

    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Shop
     */
    public function getShop(): ?Shop;

    /**
     * @return bool
     */
    public function hasAccount(): bool;

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function hasTaxFields(): bool;

    /**
     * @return bool
     */
    public function usesOrderMode(): bool;
}
