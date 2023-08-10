<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Service;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;

class AccountSettingsService
{
    /**
     * @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface
     */
    private $pdkAccountRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface $pdkAccountRepository
     */
    public function __construct(PdkAccountRepositoryInterface $pdkAccountRepository)
    {
        $this->pdkAccountRepository = $pdkAccountRepository;
    }

    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    public function getAccount(): ?Account
    {
        return $this->pdkAccountRepository->getAccount();
    }

    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     * @deprecated use getCarriers()
     */
    public function getCarrierOptions(): CarrierCollection
    {
        return $this->getCarriers();
    }

    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function getCarriers(): CarrierCollection
    {
        $shop = $this->getShop();

        if (! $shop || ! $shop->carriers) {
            return new CarrierCollection();
        }

        $allowedCarriers = Platform::get('allowedCarriers');

        $carriers = $shop->carriers
            ->filter(function (Carrier $carrier) use ($allowedCarriers) {
                $isAllowed = in_array($carrier->name, $allowedCarriers, true);

                return $isAllowed && $carrier->enabled && $carrier->capabilities;
            })
            ->values()->all();

        usort($carriers, function (Carrier $carrier1, Carrier $carrier2) {
            return Pdk::get('sortOrderCarriers')[$carrier1->name] > Pdk::get('sortOrderCarriers')[$carrier2->name];
        });

        return new CarrierCollection($carriers);
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
     */
    public function hasAccount(): bool
    {
        return null !== $this->getAccount();
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function hasTaxFields(): bool
    {
        return $this->hasAccount()
            && (new Collection(Pdk::get('carriersWithTaxFields') ?? []))
                ->contains(function (string $carrier) {
                    return $this->hasCarrier($carrier);
                });
    }

    /**
     * @return bool
     */
    public function usesOrderMode(): bool
    {
        return $this->getAccount()->generalSettings->orderMode;
    }

    /**
     * @param  string $carrierName
     *
     * @return bool
     */
    protected function hasCarrier(string $carrierName): bool
    {
        return $this->hasAccount()
            && $this->getCarriers()
                ->contains(function (Carrier $carrier) use ($carrierName) {
                    return $carrier->name === $carrierName;
                });
    }
}
