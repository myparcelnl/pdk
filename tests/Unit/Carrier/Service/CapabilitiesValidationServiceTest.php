<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleCapabilitiesResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;

use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('checkout', 'capabilities');

usesShared(new UsesMockPdkInstance(), new UsesAccountMock(), new UsesSdkApiMock());

it('takes the highest defined max weight when one carrier has no max defined', function () {
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        [
            'carrier'            => 'POSTNL',
            'contract'           => ['id' => 1, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE'],
            'options'            => (object) [],
            'physicalProperties' => [
                'weight' => [
                    'min' => ['value' => 1, 'unit' => 'g'],
                    'max' => ['value' => 23000, 'unit' => 'g'],
                ],
            ],
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ],
        [
            'carrier'            => 'BPOST',
            'contract'           => ['id' => 2, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE'],
            'options'            => (object) [],
            // No physicalProperties → no max defined.
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ],
    ]));

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    $weights = $service->getPackageTypeWeights('NL', ['package' => 'PACKAGE']);

    expect($weights)->toBe(['package' => 23000]);
});

it('returns null when no carrier in the response defines a max weight', function () {
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        [
            'carrier'            => 'POSTNL',
            'contract'           => ['id' => 1, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE'],
            'options'            => (object) [],
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ],
    ]));

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    $weights = $service->getPackageTypeWeights('NL', ['package' => 'PACKAGE']);

    expect($weights)->toBe(['package' => null]);
});
