<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Service;

use MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

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
                            'carrier' => RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS,
                        ],
                        [
                            'carrier' => RefCapabilitiesSharedCarrierV2::DHL_PARCEL_CONNECT,
                        ],
                        [
                            'carrier' => RefCapabilitiesSharedCarrierV2::POSTNL,
                        ],
                        [
                            'carrier' => RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU,
                        ],
                    ],
                ],
            ],
        ])
    );
});

it('gets carriers in the same order as stored', function () {
    /** @var AccountSettingsServiceInterface $service */
    $service = Pdk::get(AccountSettingsServiceInterface::class);

    $carriers = $service->getCarriers();

    expect(
        $carriers->pluck('carrier')
            ->all()
    )
        ->toEqual([
            RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS,
            RefCapabilitiesSharedCarrierV2::DHL_PARCEL_CONNECT,
            RefCapabilitiesSharedCarrierV2::POSTNL,
            RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU,
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
