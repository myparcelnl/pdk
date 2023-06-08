<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\App\Account\Repository\AbstractPdkAccountRepository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

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
     * @var string
     */
    private $invalidApiKey;

    /**
     * @var \MyParcelNL\Pdk\Account\Model\Account|null
     */
    private $storedAccount;

    /**
     * @param  array                                                $data
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface    $storage
     * @param  \MyParcelNL\Pdk\Account\Repository\AccountRepository $accountRepository
     *
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(array $data = [], StorageInterface $storage, AccountRepository $accountRepository)
    {
        parent::__construct($storage, $accountRepository);
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
     * @param  null|string $apiKey
     *
     * @return bool
     */
    public function isInvalidApiKey(?string $apiKey): bool
    {
        return $this->invalidApiKey === $apiKey;
    }

    /**
     * @param  string $apiKey
     *
     * @return void
     */
    public function markApiKeyAsInvalid(string $apiKey): void
    {
        $this->invalidApiKey = $apiKey;
    }

    /**
     * @return void
     */
    public function markApiKeyAsValid(): void
    {
        $this->invalidApiKey = null;
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Account\Model\Account $account
     *
     * @return void
     */
    public function store(?Account $account): ?Account
    {
        $this->storedAccount = $account;

        return $account;
    }
}
