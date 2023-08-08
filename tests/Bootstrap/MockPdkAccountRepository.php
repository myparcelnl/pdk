<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\App\Account\Repository\AbstractPdkAccountRepository;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
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

    /**
     * @var \MyParcelNL\Pdk\Account\Model\Account|null
     */
    private $storedAccount;

    /**
     * @param  null|array                                                    $data
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface             $storage
     * @param  \MyParcelNL\Pdk\Account\Repository\AccountRepository          $accountRepository
     * @param  \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface $settingsRepository
     *
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(
        ?array                      $data = [],
        StorageInterface            $storage,
        AccountRepository           $accountRepository,
        SettingsRepositoryInterface $settingsRepository
    ) {
        parent::__construct($storage, $accountRepository, $settingsRepository);
        $this->storedAccount = null === $data ? null : new Account(array_replace_recursive(self::DEFAULT_DATA, $data));
    }

    /**
     * @return void
     */
    public function deleteAccount(): void
    {
        $this->store(null);
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Account\Model\Account $account
     *
     * @return void
     * @noinspection PhpOverridingMethodVisibilityInspection
     */
    public function store(?Account $account): ?Account
    {
        $this->storedAccount = $account;

        return $this->save('account', $account);
    }

    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    protected function getFromStorage(): ?Account
    {
        return $this->storedAccount;
    }
}
