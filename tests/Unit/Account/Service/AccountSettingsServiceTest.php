<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Service;

use MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use function MyParcelNL\Pdk\Tests\factory;

beforeEach(function () {
    factory(Account::class)
        ->with([
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
                            'name'           => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                            'subscriptionId' => '8277',
                            'enabled'        => true,
                        ],
                        [
                            'name'           => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                            'subscriptionId' => '2312',
                            'enabled'        => true,
                        ],
                        [
                            'name'           => Carrier::CARRIER_POSTNL_NAME,
                            'subscriptionId' => '2312',
                            'enabled'        => true,
                        ],
                        [
                            'name'           => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                            'subscriptionId' => '4689',
                            'enabled'        => false,
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
        ->store();
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
