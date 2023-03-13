<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AbstractAccountRepository;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Storage\StorageInterface;

class MockAccountRepository extends AbstractAccountRepository
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
                'carrierOptions'        => [
                    [
                        'carrier' => [
                            'name'    => 'postnl',
                            'enabled' => true,
                        ],
                    ],
                    [
                        'carrier' => [
                            'name'           => 'dhlforyou',
                            'subscriptionId' => '8277',
                            'enabled'        => true,
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * @var \MyParcelNL\Pdk\Account\Model\Account|null
     */
    private $storedAccount;

    /** @noinspection PhpOptionalBeforeRequiredParametersInspection */

    public function __construct(array $data = [], StorageInterface $storage, ApiServiceInterface $api)
    {
        parent::__construct($storage, $api);

        $this->storedAccount = $data === null ? null : new Account(array_replace_recursive(self::DEFAULT_DATA, $data));
    }

    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    public function getFromStorage(): ?Account
    {
        return $this->storedAccount;
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Account\Model\Account $account
     *
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    public function store(?Account $account): ?Account
    {
        $this->storedAccount = $account;

        return $account;
    }
}
