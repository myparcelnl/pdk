<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Service;

use MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

beforeAll(function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $repository */
    $repository = Pdk::get(PdkAccountRepositoryInterface::class);

    $repository->store(
        new Account([
            'id'         => '1234567',
            'platformId' => 1,
            'status'     => 2,
            'shops'      => [
                [
                    'id'         => '444',
                    'accountId'  => '1234567',
                    'platformId' => 1,
                    'name'       => 'ILoveCarriers',
                    'carriers'   => [
                        // Messed up sorting on purpose
                        [
                            'name'    => Carrier::CARRIER_DHL_EUROPLUS_NAME,
                            'enabled' => true,
                        ],
                        [
                            'name'    => Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
                            'enabled' => true,
                        ],
                        [
                            'name'       => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                            'contractId' => '8277',
                            'enabled'    => true,
                        ],
                        [
                            'name'       => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                            'contractId' => '2312',
                            'enabled'    => true,
                        ],
                        [
                            'name'       => Carrier::CARRIER_POSTNL_NAME,
                            'contractId' => '2312',
                            'enabled'    => true,
                        ],
                        [
                            'name'       => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                            'contractId' => '4689',
                            'enabled'    => false,
                        ],
                        [
                            'name'    => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                            'enabled' => true,
                        ],
                        [
                            'name'    => Carrier::CARRIER_POSTNL_NAME,
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
        ])
    );
});

it('gets carriers in correct order', function () {
    /** @var AccountSettingsServiceInterface $service */
    $service = Pdk::get(AccountSettingsServiceInterface::class);

    $carriers = $service->getCarriers();

    expect(
        $carriers->pluck('externalIdentifier')
            ->all()
    )
        ->toEqual([
            Carrier::CARRIER_POSTNL_NAME,
            sprintf('%s:2312', Carrier::CARRIER_POSTNL_NAME),
            Carrier::CARRIER_DHL_FOR_YOU_NAME,
            sprintf('%s:2312', Carrier::CARRIER_DHL_FOR_YOU_NAME),
            sprintf('%s:8277', Carrier::CARRIER_DHL_FOR_YOU_NAME),
            Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
            Carrier::CARRIER_DHL_EUROPLUS_NAME,
        ]);
});

it('checks subscription features in non-existent account', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $repository */
    $repository = Pdk::get(PdkAccountRepositoryInterface::class);

    $repository->store(null);

    $result = AccountSettings::hasSubscriptionFeature(Account::FEATURE_ORDER_NOTES);

    expect($result)->toBeFalse();
});

it('checks subscription features in account', function () {
    TestBootstrapper::hasAccount();

    factory(Account::class)
        ->withSubscriptionFeatures([
            Account::FEATURE_ORDER_NOTES,
        ])
        ->store();

    $result = AccountSettings::hasSubscriptionFeature(Account::FEATURE_ORDER_NOTES);

    expect($result)->toBeTrue();
});

it('checks account small package contract', function () {
    TestBootstrapper::hasAccount();

    factory(Account::class)
        ->withGeneralSettings([
            'hasCarrierSmallPackageContract' => true,
        ])
        ->store();

    $result = AccountSettings::hasCarrierSmallPackageContract();

    expect($result)->toBeTrue();

    TestBootstrapper::hasAccount();

    factory(Account::class)
        ->withGeneralSettings([
            'hasCarrierSmallPackageContract' => false,
        ])
        ->store();

    $result = AccountSettings::hasCarrierSmallPackageContract();

    expect($result)->toBeFalse();
});
