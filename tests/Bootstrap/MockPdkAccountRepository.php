<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\App\Account\Repository\AbstractPdkAccountRepository;

final class MockPdkAccountRepository extends AbstractPdkAccountRepository
{
    private const DEFAULT_DATA = [
        'id'              => '21000',
        'platformId'      => 1,
        'status'          => 2,
        'contactInfo'     => [
            'name'  => 'Felicia Parcel',
            'email' => 'test@myparcel.nl',
        ],
        'generalSettings' => [],
        'shops'           => [
            [
                'id'                    => '23999',
                'accountId'             => '21000',
                'platformId'            => 1,
                'name'                  => 'MyParcel',
                'hidden'                => false,
                'billing'               => [],
                'deliveryAddress'       => [],
                'generalSettings'       => [],
                'return'                => [],
                'shipmentOptions'       => [],
                'trackTrace'            => [],
                'carrierConfigurations' => [],
                'carriers'              => [
                    [
                        'name'    => 'postnl',
                        'enabled' => true,
                    ],
                    [
                        'name'           => 'dhlforyou',
                        'subscriptionId' => '8277',
                        'enabled'        => true,
                    ],
                ],
            ],
        ],
    ];

    private ?Account $storedAccount = null;

    public function deleteAccount(): void
    {
        $this->store(null);
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Account\Model\Account $account
     *
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    public function store(?Account $account): ?Account
    {
        $this->storedAccount = $account;

        return $this->save('account', $account);
    }

    protected function getFromStorage(): ?Account
    {
        return $this->storedAccount;
    }
}
