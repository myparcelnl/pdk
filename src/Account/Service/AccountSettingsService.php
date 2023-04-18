<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Service;

use Exception;
use MyParcelNL\Pdk\Account\Contract\AccountRepositoryInterface;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\Pdk;

class AccountSettingsService
{
    /**
     * @var \MyParcelNL\Pdk\Account\Contract\AccountRepositoryInterface
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
        try {
            return $this->accountRepository->getAccount();
        } catch (Exception $e) {
            return null;
        }
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

        $allowedCarriers = Pdk::get('allowedCarriers');

        return $shop->carrierOptions
            ->filter(function (CarrierOptions $carrierOption) use ($allowedCarriers) {
                $isAllowed = in_array($carrierOption->carrier->name, $allowedCarriers, true);

                return $isAllowed && $carrierOption->carrier->enabled;
            })
            ->values();
    }

    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Shop
     */
    public function getShop(): ?Shop
    {
        $account = $this->getAccount();

        return $account ? $account->shops->first() : null;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function hasTaxFields(): bool
    {
        return (new Collection(Pdk::get('carriersWithTaxFields') ?? []))
            ->contains(function (string $carrier) {
                return $this->hasCarrier($carrier);
            });
    }

    /**
     * @param  string $carrierName
     *
     * @return bool
     */
    protected function hasCarrier(string $carrierName): bool
    {
        return $this->getCarrierOptions()
            ->contains(function (CarrierOptions $carrierOption) use ($carrierName) {
                return $carrierOption->carrier->name === $carrierName;
            });
    }
}
