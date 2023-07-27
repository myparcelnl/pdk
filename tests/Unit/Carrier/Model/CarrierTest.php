<?php
/** @noinspection PhpUndefinedMethodInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockConfig;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

const DHL_FOR_YOU_CUSTOM_IDENTIFIER = Carrier::CARRIER_DHL_FOR_YOU_NAME . ':' . MockConfig::SUBSCRIPTION_ID_DHL_FOR_YOU;

usesShared(new UsesMockPdkInstance());

it('creates default carrier for platform', function (string $platform) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdk $mockPdk */
    $mockPdk = Pdk::get(PdkInterface::class);

    $oldPlatform = $mockPdk->get('platform');
    $mockPdk->set('platform', $platform);

    $carrier = new Carrier();

    assertMatchesJsonSnapshot(
        json_encode($carrier->except(['capabilities', 'returnCapabilities'], Arrayable::SKIP_NULL))
    );

    $mockPdk->set('platform', $oldPlatform);
})->with('platforms');

it('instantiates carrier', function (string $carrierName) {
    $carrier = new Carrier(['name' => $carrierName]);

    assertMatchesJsonSnapshot(json_encode($carrier));
})->with('carrierNames');

it('generates external identifier', function (array $input, string $identifier) {
    $carrier = new Carrier($input);

    expect($carrier->externalIdentifier)
        ->toBe($identifier)
        ->and($carrier->type)
        ->toBe(isset($input['subscriptionId']) ? Carrier::TYPE_CUSTOM : Carrier::TYPE_MAIN);
})->with([
    'id' => [
        'input'      => ['id' => Carrier::CARRIER_DHL_FOR_YOU_ID],
        'identifier' => Carrier::CARRIER_DHL_FOR_YOU_NAME,
    ],

    'name' => [
        'input'      => ['name' => Carrier::CARRIER_DHL_FOR_YOU_NAME],
        'identifier' => Carrier::CARRIER_DHL_FOR_YOU_NAME,
    ],

    'subscriptionId' => [
        'input'      => ['subscriptionId' => MockConfig::SUBSCRIPTION_ID_DHL_FOR_YOU],
        'identifier' => DHL_FOR_YOU_CUSTOM_IDENTIFIER,
    ],

    'name and subscriptionId' => [
        'input'      => [
            'name'           => Carrier::CARRIER_DHL_FOR_YOU_NAME,
            'subscriptionId' => MockConfig::SUBSCRIPTION_ID_DHL_FOR_YOU,
        ],
        'identifier' => DHL_FOR_YOU_CUSTOM_IDENTIFIER,
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
        array_reduce(Carrier::CARRIER_NAME_ID_MAP, function (array $carry, int $id) {
            $name           = array_flip(Carrier::CARRIER_NAME_ID_MAP)[$id];
            $subscriptionId = 124230;

            $carry[$name] = [
                [
                    'id'                 => $id,
                    'name'               => $name,
                    'subscriptionId'     => $subscriptionId,
                    'externalIdentifier' => "$name:$subscriptionId",
                ],
            ];

            return $carry;
        }, [])
    )
    ->with([
        'id and name'                 => [[['id'], ['name']]],
        'id, name and subscriptionId' => [[['id', 'subscriptionId'], ['name', 'subscriptionId']]],
        'external identifier'         => [[['externalIdentifier'], ['externalIdentifier']]],
    ]);

it('instantiates carrier from external identifier', function (string $identifier) {
    $carrier = new Carrier(['externalIdentifier' => $identifier]);

    assertMatchesJsonSnapshot(json_encode($carrier->toArrayWithoutNull()));
})->with([
    'subscription carrier' => [DHL_FOR_YOU_CUSTOM_IDENTIFIER],
    'default carrier'      => [Carrier::CARRIER_DHL_FOR_YOU_NAME],
]);
