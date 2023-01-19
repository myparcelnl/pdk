<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Service;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Account\Repository\AccountRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;

class AccountSettingsService
{
    /**
     * @var \MyParcelNL\Pdk\Account\Repository\AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    public function getAccount(): ?Account
    {
        return $this->accountRepository->getAccount();
    }

    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection
     */
    public function getCarrierOptions(): CarrierOptionsCollection
    {
        $shop = $this->getShop();

        if (! $shop || ! $shop->carrierOptions) {
            return new CarrierOptionsCollection();
        }

        return $shop->carrierOptions->filter(function (CarrierOptions $carrierOption) {
            return $carrierOption->carrier->enabled;
        });
    }

    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Shop
     */
    public function getShop(): ?Shop
    {
        $account = $this->getAccount();

        return $account ? $account->shops->first() : null;
    }
}
