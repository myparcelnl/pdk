<?php

/** @noinspection PhpUndefinedMethodInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Tests\Bootstrap\MockConfig;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use MyParcelNL\Sdk\src\Support\Collection;

use function MyParcelNL\Pdk\Tests\mockPlatform;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

const DHL_FOR_YOU_CUSTOM_IDENTIFIER = Carrier::CARRIER_DHL_FOR_YOU_NAME . ':' . MockConfig::SUBSCRIPTION_ID_DHL_FOR_YOU;

usesShared(new UsesEachMockPdkInstance());

it('creates default carrier for platform', function (string $platform) {
    $reset = mockPlatform($platform);

    $carrier = new Carrier();

    assertMatchesJsonSnapshot(
        json_encode($carrier->except(['capabilities', 'returnCapabilities'], Arrayable::SKIP_NULL))
    );

    $reset();
})->with('platforms');


it('does not return a carrier for an unknown ID', function (string $platform) {
    $reset = mockPlatform($platform);

    $carrier = new Carrier(['id' => 1337]);

    expect($carrier->name)
        ->toBeNull();

    $reset();
})->with('platforms');


it('generates external identifier', function (array $input, string $identifier) {
    $carrier = new Carrier($input);

    expect($carrier->externalIdentifier)
        ->toBe($identifier);
})->with([
    'id' => [
        'input'      => ['id' => Carrier::CARRIER_DPD_ID],
        'identifier' => Carrier::CARRIER_DPD_NAME,
    ],

    'name' => [
        'input'      => ['name' => Carrier::CARRIER_BPOST_NAME],
        'identifier' => Carrier::CARRIER_BPOST_NAME,
    ],

    'name and contractId' => [
        'input'      => [
            'name'       => Carrier::CARRIER_DHL_FOR_YOU_NAME,
            'contractId' => MockConfig::SUBSCRIPTION_ID_DHL_FOR_YOU,
        ],
        'identifier' => DHL_FOR_YOU_CUSTOM_IDENTIFIER,
    ],
]);

it('determines type based on contract id', function (array $input, string $type) {
    $carrier = new Carrier($input);

    expect($carrier->type)->toBe($type);
})->with([
    'name' => [
        'input' => [
            'name' => Carrier::CARRIER_DHL_FOR_YOU_NAME,
        ],
        'type'  => Carrier::TYPE_MAIN,
    ],

    'name and contractId' => [
        'input' => [
            'name'       => Carrier::CARRIER_DHL_FOR_YOU_NAME,
            'contractId' => MockConfig::SUBSCRIPTION_ID_DHL_FOR_YOU,
        ],
        'type'  => Carrier::TYPE_CUSTOM,
    ],
]);

it('generates the same data with either name or id', function (array $values, array $keys) {
    $carrierA = new Carrier(Arr::only($values, $keys[0]));
    $carrierB = new Carrier(Arr::only($values, $keys[1]));

    $arrayA = $carrierA->toArrayWithoutNull();
    $arrayB = $carrierB->toArrayWithoutNull();

    expect($arrayA)
        ->not()
        ->toBeEmpty()
        ->and($arrayA)
        ->toEqual($arrayB);
})
    ->with(
        array_reduce([
            Carrier::CARRIER_DHL_EUROPLUS_NAME,
            Carrier::CARRIER_DHL_FOR_YOU_NAME,
            Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
            Carrier::CARRIER_POSTNL_NAME,
        ], function (array $carry, string $name) {
            $id         = Carrier::CARRIER_NAME_ID_MAP[$name];
            $contractId = 124230;

            $carry[$name] = [
                [
                    'id'                 => $id,
                    'name'               => $name,
                    'contractId'         => $contractId,
                    'externalIdentifier' => "$name:$contractId",
                ],
            ];

            return $carry;
        }, [])
    )
    ->with([
        'id and name'             => [[['id'], ['name']]],
        'id, name and contractId' => [[['id', 'contractId'], ['name', 'contractId']]],
        'external identifier'     => [[['externalIdentifier'], ['externalIdentifier']]],
    ]);

it('instantiates carriers from name', function (string $platform) {
    $reset = mockPlatform($platform);

    $carriers    = new Collection(Platform::getCarriers());
    $newCarriers = $carriers->map(function (Carrier $carrier) {
        return new Carrier(['name' => $carrier->name]);
    });

    assertMatchesJsonSnapshot(json_encode($newCarriers->toArrayWithoutNull()));
    $reset();
})->with('platforms');

it('instantiates carrier from external identifier', function (string $identifier) {
    $carrier = new Carrier(['externalIdentifier' => $identifier]);

    assertMatchesJsonSnapshot(json_encode($carrier->toArrayWithoutNull()));
})->with([
    'subscription carrier' => [DHL_FOR_YOU_CUSTOM_IDENTIFIER],
    'default carrier'      => [Carrier::CARRIER_DHL_FOR_YOU_NAME],
]);
