<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Base\Support\SettingKey;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorage;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleCapabilitiesResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('checkout', 'capabilities');

usesShared(new UsesMockPdkInstance(), new UsesAccountMock(), new UsesSdkApiMock());

/**
 * Build a single capabilities result entry for the mock API response.
 */
function capabilityResult(
    string $carrier,
    int $contractId,
    array $packageTypes,
    array $deliveryTypes = ['STANDARD_DELIVERY'],
    int $weightMin = 1,
    int $weightMax = 23000,
    string $weightUnit = 'g'
): array {
    return [
        'carrier'            => $carrier,
        'contract'           => ['id' => $contractId, 'type' => 'MAIN'],
        'packageTypes'       => $packageTypes,
        'options'            => (object) [],
        'physicalProperties' => [
            'weight' => [
                'min' => ['value' => $weightMin, 'unit' => $weightUnit],
                'max' => ['value' => $weightMax, 'unit' => $weightUnit],
            ],
        ],
        'deliveryTypes'      => $deliveryTypes,
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
    ];
}

/**
 * Store carrier settings for multiple carriers at once to avoid overwriting.
 *
 * @param  array<string, bool> $carriers Map of carrier name => deliveryOptionsEnabled
 */
function storeCarrierSettings(array $carriers): void
{
    $settingsFactory = factory(Settings::class);

    foreach ($carriers as $carrierName => $enabled) {
        $carrierSettingsFactory = factory(CarrierSettings::class, $carrierName)
            ->withDeliveryOptionsEnabled($enabled);

        if ($enabled) {
            $carrierSettingsFactory = $carrierSettingsFactory->withDeliveryOptions();
        }

        $settingsFactory = $settingsFactory->withCarrier($carrierName, $carrierSettingsFactory);
    }

    $settingsFactory->store();
}

/**
 * Build a minimal cart with a recipient country and default package type.
 */
function makeCart(string $cc, int $weight = 1000, int $quantity = 1): PdkCart
{
    return new PdkCart([
        'shippingMethod' => [
            'shippingAddress' => ['cc' => $cc],
        ],
        'lines' => [
            [
                'quantity' => $quantity,
                'product'  => [
                    'weight'        => $weight,
                    'isDeliverable' => true,
                ],
            ],
        ],
    ]);
}

/**
 * Reset the memory cache storage so that stale CarrierRepository data
 * (from UsesAccountMock's default account) does not leak into the test.
 *
 * Must be called after factory(Shop::class)->store() when the test
 * configures carriers with specific package types.
 */
function resetStorageCache(): void
{
    /** @var MockMemoryCacheStorage $storage */
    $storage = Pdk::get(StorageInterface::class);
    $storage->reset();
}

/**
 * Configure one capability-driven carrier for pickup availability tests.
 */
function configurePickupCarrier(string $carrier, bool $allowPickup = true, int $emptyParcelWeight = 0): void
{
    factory(Settings::class)
        ->withOrder(factory(OrderSettings::class)->withEmptyParcelWeight($emptyParcelWeight))
        ->withCarrier(
            $carrier,
            factory(CarrierSettings::class, $carrier)
                ->withDeliveryOptions()
                ->withAllowStandardDelivery(true)
                ->withAllowPickupLocations($allowPickup)
        )
        ->store();

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(
                    factory(Carrier::class)
                        ->withCarrier($carrier)
                        ->withCapabilityPackageTypes(['PACKAGE'])
                )
        )
        ->store();

    resetStorageCache();
}

/**
 * Build a cart whose deliverable product weights match the supplied list.
 *
 * @param  int[] $weights
 */
function makeCartWithWeights(string $cc, array $weights): PdkCart
{
    return new PdkCart([
        'shippingMethod' => [
            'shippingAddress' => ['cc' => $cc],
        ],
        'lines'          => array_map(
            static function (int $weight): array {
                return [
                    'quantity' => 1,
                    'product'  => [
                        'weight'        => $weight,
                        'isDeliverable' => true,
                    ],
                ];
            },
            $weights
        ),
    ]);
}

/**
 * Enqueue capabilities responses for each package type.
 *
 * getPackageTypeWeights() makes one capabilities call per allowed package type.
 * Each call needs a mock response.
 *
 * @param  array $responsesPerType V2 package type => capabilities results array
 */
function enqueueCapabilitiesPerType(array $responsesPerType): void
{
    foreach ($responsesPerType as $results) {
        MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse($results));
    }
}

it('excludes carrier when package type is not in capabilities', function () {
    storeCarrierSettings([
        RefCapabilitiesSharedCarrierV2::POSTNL      => true,
        RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU  => true,
    ]);

    // Both carriers support PACKAGE in contract definitions.
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                    ->withCapabilityPackageTypes(['PACKAGE']))
                ->push(factory(Carrier::class)
                    ->withCarrier(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU)
                    ->withCapabilityPackageTypes(['PACKAGE']))
        )
        ->store();

    resetStorageCache();

    // Capabilities call for PACKAGE: only PostNL available for NL.
    enqueueCapabilitiesPerType([
        'PACKAGE' => [capabilityResult('POSTNL', 100, ['PACKAGE'])],
    ]);

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $result = $service->createAllCarrierSettings(makeCart('NL'));

    $postnlId = FrontendData::getLegacyCarrierIdentifier(RefCapabilitiesSharedCarrierV2::POSTNL);
    $dhlId    = FrontendData::getLegacyCarrierIdentifier(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU);

    expect($result['packageType'])->toBe(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME)
        ->and($result['carrierSettings'])->toHaveKey($postnlId)
        ->and($result['carrierSettings'])->not->toHaveKey($dhlId);
});

it('sends isBusiness on the checkout carrier-filter request based on the recipient company', function (?string $company, bool $expected) {
    storeCarrierSettings([RefCapabilitiesSharedCarrierV2::POSTNL => true]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                    ->withCapabilityPackageTypes(['PACKAGE']))
        )
        ->store();

    resetStorageCache();

    // Enough responses for the per-type weight aggregation plus the carrier-filter call.
    foreach (range(1, 5) as $ignored) {
        MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([capabilityResult('POSTNL', 100, ['PACKAGE'])]));
    }

    $cart = new PdkCart([
        'shippingMethod' => [
            'shippingAddress' => ['cc' => 'NL', 'company' => $company],
        ],
        'lines' => [
            ['quantity' => 1, 'product' => ['weight' => 1000, 'isDeliverable' => true]],
        ],
    ]);

    // The cart address is a bare Address: it derives the flag but never stores the company (PII-free).
    expect($cart->shippingMethod->shippingAddress->toArray())->not->toHaveKey('company');

    Pdk::get(DeliveryOptionsServiceInterface::class)->createAllCarrierSettings($cart);

    $body = json_decode((string) MockSdkApiHandler::getHandler()->getLastRequest()->getBody(), true);

    expect($body['recipient'])->toHaveKey('isBusiness', $expected);
})->with([
    'business (company entered)' => ['Acme B.V.', true],
    'consumer (no company)'      => [null, false],
]);

it('excludes carriers when weight exceeds maximum', function () {
    storeCarrierSettings([RefCapabilitiesSharedCarrierV2::POSTNL => true]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                    ->withCapabilityPackageTypes(['PACKAGE']))
        )
        ->store();

    resetStorageCache();

    // Max weight 23kg, cart weighs 25kg — carrier should be excluded.
    enqueueCapabilitiesPerType([
        'PACKAGE' => [capabilityResult('POSTNL', 100, ['PACKAGE'], ['STANDARD_DELIVERY'], 1, 23000)],
    ]);

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $result = $service->createAllCarrierSettings(makeCart('NL', 25000));

    expect($result['packageType'])->toBe(DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME);
});

it('excludes carriers when weight is below minimum', function () {
    storeCarrierSettings([RefCapabilitiesSharedCarrierV2::POSTNL => true]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                    ->withCapabilityPackageTypes(['PACKAGE']))
        )
        ->store();

    resetStorageCache();

    // Min weight 100g, cart weighs 50g — carrier should be excluded.
    enqueueCapabilitiesPerType([
        'PACKAGE' => [capabilityResult('POSTNL', 100, ['PACKAGE'], ['STANDARD_DELIVERY'], 100, 23000)],
    ]);

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $result = $service->createAllCarrierSettings(makeCart('NL', 50));

    expect($result['packageType'])->toBe(DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME);
});

it('excludes carriers with delivery options disabled', function () {
    storeCarrierSettings([
        RefCapabilitiesSharedCarrierV2::POSTNL      => true,
        RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU  => false,
    ]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                    ->withCapabilityPackageTypes(['PACKAGE']))
                ->push(factory(Carrier::class)
                    ->withCarrier(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU)
                    ->withCapabilityPackageTypes(['PACKAGE']))
        )
        ->store();

    resetStorageCache();

    enqueueCapabilitiesPerType([
        'PACKAGE' => [
            capabilityResult('POSTNL', 100, ['PACKAGE']),
            capabilityResult('DHL_FOR_YOU', 200, ['PACKAGE']),
        ],
    ]);

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $result = $service->createAllCarrierSettings(makeCart('NL'));

    $enabledId  = FrontendData::getLegacyCarrierIdentifier(RefCapabilitiesSharedCarrierV2::POSTNL);
    $disabledId = FrontendData::getLegacyCarrierIdentifier(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU);

    expect($result['carrierSettings'])->toHaveKey($enabledId)
        ->and($result['carrierSettings'])->not->toHaveKey($disabledId);
});

it('keeps valid carriers when another carrier setting is malformed', function () {
    $carrierName = RefCapabilitiesSharedCarrierV2::getAllowableEnumValues()[0];

    storeCarrierSettings([$carrierName => true]);

    /** @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settingsKey        = Pdk::get('createSettingsKey')(CarrierSettings::ID);
    $carrierSettings    = $settingsRepository->get($settingsKey);

    $settingsRepository->store($settingsKey, array_merge($carrierSettings, ['invalid' => 'invalid']));

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier($carrierName)
                    ->withCapabilityPackageTypes(['PACKAGE']))
        )
        ->store();

    resetStorageCache();

    enqueueCapabilitiesPerType([
        'PACKAGE' => [capabilityResult($carrierName, 100, ['PACKAGE'])],
    ]);

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);
    $result  = $service->createAllCarrierSettings(makeCart('NL'));

    $carrierId = FrontendData::getLegacyCarrierIdentifier($carrierName);

    expect($result['carrierSettings'])->toHaveKey($carrierId);
});

it('passes contract ID from capabilities to carrier settings output', function () {
    storeCarrierSettings([RefCapabilitiesSharedCarrierV2::POSTNL => true]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                    ->withCapabilityPackageTypes(['PACKAGE']))
        )
        ->store();

    resetStorageCache();

    enqueueCapabilitiesPerType([
        'PACKAGE' => [capabilityResult('POSTNL', 777, ['PACKAGE'])],
    ]);

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $result = $service->createAllCarrierSettings(makeCart('NL'));

    $carrierId = FrontendData::getLegacyCarrierIdentifier(RefCapabilitiesSharedCarrierV2::POSTNL);

    expect($result['carrierSettings'][$carrierId]['contractId'])->toBe(777);
});

it('skips mailbox package type when mailbox percentage exceeds 100%', function () {
    storeCarrierSettings([RefCapabilitiesSharedCarrierV2::POSTNL => true]);

    // Carrier supports both PACKAGE and MAILBOX in contract definitions.
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                    ->withCapabilityPackageTypes(['PACKAGE', 'MAILBOX']))
        )
        ->store();

    resetStorageCache();

    // Two capabilities calls: one for PACKAGE (heavier = tried during upgrade), one for MAILBOX.
    // Order depends on weight sorting — PACKAGE has higher max weight so is "larger".
    enqueueCapabilitiesPerType([
        'PACKAGE' => [capabilityResult('POSTNL', 100, ['PACKAGE'], ['STANDARD_DELIVERY'], 1, 23000)],
        'MAILBOX' => [capabilityResult('POSTNL', 100, ['MAILBOX'], ['STANDARD_DELIVERY'], 1, 2000)],
    ]);

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    // Cart with 5 items that each fill 25% of mailbox = 125% total → exceeds 100%.
    // Mailbox is skipped, falls through to PACKAGE as upgrade.
    $result = $service->createAllCarrierSettings(new PdkCart([
        'shippingMethod' => [
            'shippingAddress' => ['cc' => 'NL'],
        ],
        'lines' => [
            [
                'quantity' => 5,
                'product'  => [
                    'weight'        => 100,
                    'isDeliverable' => true,
                    'settings'      => [
                        'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        'fitInMailbox' => 4,
                    ],
                ],
            ],
        ],
    ]));

    // Mailbox percentage > 100% → skipped → upgrade to package.
    expect($result['packageType'])->toBe(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME);
});

it('upgrades to next fitting package type when desired type exceeds weight', function () {
    storeCarrierSettings([RefCapabilitiesSharedCarrierV2::POSTNL => true]);

    // Carrier supports MAILBOX and PACKAGE in contract definitions.
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                    ->withCapabilityPackageTypes(['PACKAGE', 'MAILBOX']))
        )
        ->store();

    resetStorageCache();

    // MAILBOX max 2000g, PACKAGE max 23000g. Cart weighs 2500g — too heavy for mailbox.
    enqueueCapabilitiesPerType([
        'PACKAGE' => [capabilityResult('POSTNL', 100, ['PACKAGE'], ['STANDARD_DELIVERY'], 1, 23000)],
        'MAILBOX' => [capabilityResult('POSTNL', 100, ['MAILBOX'], ['STANDARD_DELIVERY'], 1, 2000)],
    ]);

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    // Cart wants mailbox but total weight 2500g exceeds mailbox max 2000g.
    $result = $service->createAllCarrierSettings(new PdkCart([
        'shippingMethod' => [
            'shippingAddress' => ['cc' => 'NL'],
        ],
        'lines' => [
            [
                'quantity' => 5,
                'product'  => [
                    'weight'        => 500,
                    'isDeliverable' => true,
                    'settings'      => [
                        'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                    ],
                ],
            ],
        ],
    ]));

    // Mailbox too heavy → upgraded to package.
    expect($result['packageType'])->toBe(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME);
});

it('normalizes max weight to grams when capabilities response uses kg', function () {
    storeCarrierSettings([RefCapabilitiesSharedCarrierV2::POSTNL => true]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                    ->withCapabilityPackageTypes(['PACKAGE', 'MAILBOX']))
        )
        ->store();

    resetStorageCache();

    // API returns mailbox max as 2 kg (= 2000 g). Without unit normalization, the PDK
    // would treat this as 2 g and reject the 1500 g cart.
    enqueueCapabilitiesPerType([
        'PACKAGE' => [capabilityResult('POSTNL', 100, ['PACKAGE'], ['STANDARD_DELIVERY'], 1, 23, 'kg')],
        'MAILBOX' => [capabilityResult('POSTNL', 100, ['MAILBOX'], ['STANDARD_DELIVERY'], 1, 2, 'kg')],
    ]);

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $result = $service->createAllCarrierSettings(new PdkCart([
        'shippingMethod' => [
            'shippingAddress' => ['cc' => 'NL'],
        ],
        'lines' => [
            [
                'quantity' => 1,
                'product'  => [
                    'weight'        => 1500,
                    'isDeliverable' => true,
                    'settings'      => [
                        'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                    ],
                ],
            ],
        ],
    ]));

    expect($result['packageType'])->toBe(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME);
});

it('uses exact weight-aware capabilities to narrow pickup without changing standard delivery', function (
    string $countryCode,
    int $weight,
    array $weightedDeliveryTypes,
    bool $expectedPickup,
    int $quantity,
    int $emptyParcelWeight
) {
    $carrier = RefCapabilitiesSharedCarrierV2::DPD;

    configurePickupCarrier($carrier, true, $emptyParcelWeight);

    // Existing unweighted package/carrier lookup, followed by one optional weighted lookup.
    MockSdkApiHandler::enqueue(
        new ExampleCapabilitiesResponse([
            capabilityResult(
                $carrier,
                100,
                ['PACKAGE'],
                [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP],
                1,
                31500
            ),
        ]),
        new ExampleCapabilitiesResponse([
            capabilityResult($carrier, 100, ['PACKAGE'], $weightedDeliveryTypes, 1, 31500),
        ])
    );

    $result = Pdk::get(DeliveryOptionsServiceInterface::class)
        ->createAllCarrierSettings(makeCart($countryCode, $weight, $quantity));

    $carrierId = FrontendData::getLegacyCarrierIdentifier($carrier);
    $settings  = $result['carrierSettings'][$carrierId];
    $body      = json_decode((string) MockSdkApiHandler::getHandler()->getLastRequest()->getBody(), true);

    $expectedWeight = ($weight * $quantity) + $emptyParcelWeight;
    $requestOptions = MockSdkApiHandler::getHandler()->getLastOptions();

    expect($body['physicalProperties']['weight'])->toBe(['value' => $expectedWeight, 'unit' => 'g'])
        ->and($requestOptions['timeout'])->toBe(2.0)
        ->and($requestOptions['connect_timeout'])->toBe(1.0)
        ->and($settings[SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP)])->toBe($expectedPickup)
        ->and($settings[SettingKey::allow(RefTypesDeliveryTypeV2::STANDARD)])->toBeTrue();
})->with([
    'NL: exact pickup limit' => [
        'NL',
        20000,
        [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP],
        true,
        1,
        0,
    ],
    'NL: one gram over pickup limit' => [
        'NL',
        20001,
        [RefTypesDeliveryTypeV2::STANDARD],
        false,
        1,
        0,
    ],
    'BE: exact pickup limit' => [
        'BE',
        20000,
        [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP],
        true,
        1,
        0,
    ],
    'BE: one gram over pickup limit' => [
        'BE',
        20001,
        [RefTypesDeliveryTypeV2::STANDARD],
        false,
        1,
        0,
    ],
    'NL: reported scenario with three 10 kg products' => [
        'NL',
        10000,
        [RefTypesDeliveryTypeV2::STANDARD],
        false,
        3,
        0,
    ],
    'NL: known product plus configured packaging weight' => [
        'NL',
        10000,
        [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP],
        true,
        1,
        250,
    ],
]);

it('uses only deliverable product weight for the weight-specific lookup', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::DPD;

    configurePickupCarrier($carrier);

    MockSdkApiHandler::enqueue(
        new ExampleCapabilitiesResponse([
            capabilityResult(
                $carrier,
                100,
                ['PACKAGE'],
                [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP],
                1,
                50000
            ),
        ]),
        new ExampleCapabilitiesResponse([
            capabilityResult(
                $carrier,
                100,
                ['PACKAGE'],
                [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP],
                1,
                50000
            ),
        ])
    );

    Pdk::get(DeliveryOptionsServiceInterface::class)->createAllCarrierSettings(new PdkCart([
        'shippingMethod' => ['shippingAddress' => ['cc' => 'NL']],
        'lines'          => [
            [
                'quantity' => 1,
                'product'  => ['weight' => 10000, 'isDeliverable' => true],
            ],
            [
                'quantity' => 1,
                'product'  => ['weight' => 25000, 'isDeliverable' => false],
            ],
        ],
    ]));

    $body = json_decode((string) MockSdkApiHandler::getHandler()->getLastRequest()->getBody(), true);

    expect($body['physicalProperties']['weight'])->toBe(['value' => 10000, 'unit' => 'g']);
});

it('does not treat incomplete product weights or packaging weight as a known total', function (
    array $productWeights,
    int $emptyParcelWeight
) {
    $carrier = RefCapabilitiesSharedCarrierV2::DPD;

    configurePickupCarrier($carrier, true, $emptyParcelWeight);

    // Leave a second response queued. It must not be consumed for an unknown total weight.
    MockSdkApiHandler::enqueue(
        new ExampleCapabilitiesResponse([
            capabilityResult(
                $carrier,
                100,
                ['PACKAGE'],
                [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP],
                50,
                31500
            ),
        ]),
        new ExampleCapabilitiesResponse([
            capabilityResult($carrier, 100, ['PACKAGE'], [RefTypesDeliveryTypeV2::STANDARD], 50, 31500),
        ])
    );

    $result = Pdk::get(DeliveryOptionsServiceInterface::class)
        ->createAllCarrierSettings(makeCartWithWeights('NL', $productWeights));

    $carrierId = FrontendData::getLegacyCarrierIdentifier($carrier);
    $settings  = $result['carrierSettings'][$carrierId];
    $body      = json_decode((string) MockSdkApiHandler::getHandler()->getLastRequest()->getBody(), true);

    expect(MockSdkApiHandler::getHandler()->count())->toBe(1)
        ->and($body)->not->toHaveKey('physicalProperties')
        ->and($settings[SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP)])->toBeTrue();
})->with([
    'all product weights missing'       => [[0], 0],
    'one of two product weights missing' => [[10000, 0], 0],
    'only packaging weight configured'  => [[0], 250],
]);

it('treats an explicitly supplied one gram product weight as known', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::DPD;

    configurePickupCarrier($carrier);

    MockSdkApiHandler::enqueue(
        new ExampleCapabilitiesResponse([
            capabilityResult($carrier, 100, ['PACKAGE'], [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP]),
        ]),
        new ExampleCapabilitiesResponse([
            capabilityResult($carrier, 100, ['PACKAGE'], [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP]),
        ])
    );

    Pdk::get(DeliveryOptionsServiceInterface::class)->createAllCarrierSettings(makeCart('NL', 1));

    $body = json_decode((string) MockSdkApiHandler::getHandler()->getLastRequest()->getBody(), true);

    expect($body['physicalProperties']['weight'])->toBe(['value' => 1, 'unit' => 'g']);
});

it('keeps the existing checkout settings when the optional weighted lookup fails', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::DPD;

    configurePickupCarrier($carrier);

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        capabilityResult(
            $carrier,
            100,
            ['PACKAGE'],
            [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP]
        ),
    ]));
    MockSdkApiHandler::getHandler()->append(
        new ConnectException('Operation timed out', new Request('POST', '/shipments/capabilities'), null, ['errno' => 28])
    );

    $result = Pdk::get(DeliveryOptionsServiceInterface::class)
        ->createAllCarrierSettings(makeCart('NL', 20001));

    $carrierId = FrontendData::getLegacyCarrierIdentifier($carrier);
    $settings  = $result['carrierSettings'][$carrierId];

    expect(MockSdkApiHandler::getHandler()->count())->toBe(0)
        ->and($settings[SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP)])->toBeTrue()
        ->and($settings[SettingKey::allow(RefTypesDeliveryTypeV2::STANDARD)])->toBeTrue();
});

it('does not make the optional weighted request when pickup is disabled by the merchant', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::DPD;

    configurePickupCarrier($carrier, false);

    MockSdkApiHandler::enqueue(
        new ExampleCapabilitiesResponse([
            capabilityResult(
                $carrier,
                100,
                ['PACKAGE'],
                [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP]
            ),
        ]),
        new ExampleCapabilitiesResponse([
            capabilityResult($carrier, 100, ['PACKAGE'], [RefTypesDeliveryTypeV2::STANDARD]),
        ])
    );

    $result = Pdk::get(DeliveryOptionsServiceInterface::class)
        ->createAllCarrierSettings(makeCart('NL', 30000));

    $carrierId = FrontendData::getLegacyCarrierIdentifier($carrier);

    expect(MockSdkApiHandler::getHandler()->count())->toBe(1)
        ->and($result['carrierSettings'][$carrierId][SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP)])
        ->toBeFalse();
});

it('applies weight-specific pickup availability to the matching carrier only', function () {
    $carrierWithoutPickup = RefCapabilitiesSharedCarrierV2::DPD;
    $carrierWithPickup    = RefCapabilitiesSharedCarrierV2::POSTNL;

    factory(Settings::class)
        ->withCarrier(
            $carrierWithoutPickup,
            factory(CarrierSettings::class, $carrierWithoutPickup)
                ->withDeliveryOptions()
                ->withAllowStandardDelivery(true)
                ->withAllowPickupLocations(true)
        )
        ->withCarrier(
            $carrierWithPickup,
            factory(CarrierSettings::class, $carrierWithPickup)
                ->withDeliveryOptions()
                ->withAllowStandardDelivery(true)
                ->withAllowPickupLocations(true)
        )
        ->store();

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(
                    factory(Carrier::class)
                        ->withCarrier($carrierWithoutPickup)
                        ->withCapabilityPackageTypes(['PACKAGE'])
                )
                ->push(
                    factory(Carrier::class)
                        ->withCarrier($carrierWithPickup)
                        ->withCapabilityPackageTypes(['PACKAGE'])
                )
        )
        ->store();

    resetStorageCache();

    MockSdkApiHandler::enqueue(
        new ExampleCapabilitiesResponse([
            capabilityResult($carrierWithoutPickup, 100, ['PACKAGE'], [
                RefTypesDeliveryTypeV2::STANDARD,
                RefTypesDeliveryTypeV2::PICKUP,
            ]),
            capabilityResult($carrierWithPickup, 200, ['PACKAGE'], [
                RefTypesDeliveryTypeV2::STANDARD,
                RefTypesDeliveryTypeV2::PICKUP,
            ]),
        ]),
        new ExampleCapabilitiesResponse([
            capabilityResult($carrierWithoutPickup, 100, ['PACKAGE'], [RefTypesDeliveryTypeV2::STANDARD]),
            capabilityResult($carrierWithPickup, 200, ['PACKAGE'], [
                RefTypesDeliveryTypeV2::STANDARD,
                RefTypesDeliveryTypeV2::PICKUP,
            ]),
        ])
    );

    $result = Pdk::get(DeliveryOptionsServiceInterface::class)
        ->createAllCarrierSettings(makeCart('NL', 20001));

    $withoutPickupId = FrontendData::getLegacyCarrierIdentifier($carrierWithoutPickup);
    $withPickupId    = FrontendData::getLegacyCarrierIdentifier($carrierWithPickup);

    expect($result['carrierSettings'][$withoutPickupId][SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP)])
        ->toBeFalse()
        ->and($result['carrierSettings'][$withPickupId][SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP)])
        ->toBeTrue();
});

it('narrows pickup only for a proven weight-specific delivery type change', function (
    array $unweightedDeliveryTypes,
    $weightedDeliveryTypes,
    bool $expectedPickup
) {
    $carrier = RefCapabilitiesSharedCarrierV2::DPD;

    configurePickupCarrier($carrier);

    $weightedCapability                  = capabilityResult($carrier, 100, ['PACKAGE']);
    $weightedCapability['deliveryTypes'] = $weightedDeliveryTypes;

    MockSdkApiHandler::enqueue(
        new ExampleCapabilitiesResponse([
            capabilityResult(
                $carrier,
                100,
                ['PACKAGE'],
                $unweightedDeliveryTypes
            ),
        ]),
        new ExampleCapabilitiesResponse([$weightedCapability])
    );

    $result = Pdk::get(DeliveryOptionsServiceInterface::class)
        ->createAllCarrierSettings(makeCart('NL', 20001));

    $carrierId = FrontendData::getLegacyCarrierIdentifier($carrier);

    expect($result['carrierSettings'][$carrierId][SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP)])
        ->toBe($expectedPickup);
})->with([
    'pickup removed for the known weight' => [
        [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP],
        [],
        false,
    ],
    'malformed weighted response' => [
        [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP],
        null,
        true,
    ],
    'malformed weighted response element' => [
        [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP],
        [RefTypesDeliveryTypeV2::STANDARD, null],
        true,
    ],
    'pickup absent with and without weight' => [
        [RefTypesDeliveryTypeV2::STANDARD],
        [RefTypesDeliveryTypeV2::STANDARD],
        true,
    ],
]);

it('uses only the uniquely matched selected contract for a weight-specific pickup restriction', function (
    array $weightedCapabilities,
    bool $expectedPickup
) {
    $carrier = RefCapabilitiesSharedCarrierV2::DPD;

    configurePickupCarrier($carrier);

    // Existing selection chooses the last indexed DPD capability, contract 200.
    MockSdkApiHandler::enqueue(
        new ExampleCapabilitiesResponse([
            capabilityResult($carrier, 100, ['PACKAGE'], [
                RefTypesDeliveryTypeV2::STANDARD,
                RefTypesDeliveryTypeV2::PICKUP,
            ]),
            capabilityResult($carrier, 200, ['PACKAGE'], [
                RefTypesDeliveryTypeV2::STANDARD,
                RefTypesDeliveryTypeV2::PICKUP,
            ]),
        ]),
        new ExampleCapabilitiesResponse($weightedCapabilities)
    );

    $result = Pdk::get(DeliveryOptionsServiceInterface::class)
        ->createAllCarrierSettings(makeCart('NL', 20001));

    $carrierId = FrontendData::getLegacyCarrierIdentifier($carrier);

    expect($result['carrierSettings'][$carrierId]['contractId'])->toBe(200)
        ->and($result['carrierSettings'][$carrierId][SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP)])
        ->toBe($expectedPickup);
})->with([
    'restriction on another contract is ignored' => [
        [
            capabilityResult(RefCapabilitiesSharedCarrierV2::DPD, 100, ['PACKAGE'], [
                RefTypesDeliveryTypeV2::STANDARD,
            ]),
            capabilityResult(RefCapabilitiesSharedCarrierV2::DPD, 200, ['PACKAGE'], [
                RefTypesDeliveryTypeV2::STANDARD,
                RefTypesDeliveryTypeV2::PICKUP,
            ]),
        ],
        true,
    ],
    'restriction on selected contract is applied' => [
        [
            capabilityResult(RefCapabilitiesSharedCarrierV2::DPD, 100, ['PACKAGE'], [
                RefTypesDeliveryTypeV2::STANDARD,
                RefTypesDeliveryTypeV2::PICKUP,
            ]),
            capabilityResult(RefCapabilitiesSharedCarrierV2::DPD, 200, ['PACKAGE'], [
                RefTypesDeliveryTypeV2::STANDARD,
            ]),
        ],
        false,
    ],
    'ambiguous selected contract fails open' => [
        [
            capabilityResult(RefCapabilitiesSharedCarrierV2::DPD, 200, ['PACKAGE'], [
                RefTypesDeliveryTypeV2::STANDARD,
            ]),
            capabilityResult(RefCapabilitiesSharedCarrierV2::DPD, 200, ['PACKAGE'], [
                RefTypesDeliveryTypeV2::STANDARD,
            ]),
        ],
        true,
    ],
]);

it('does not leak weight-specific pickup availability into a later cart', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::DPD;

    configurePickupCarrier($carrier);

    MockSdkApiHandler::enqueue(
        new ExampleCapabilitiesResponse([
            capabilityResult(
                $carrier,
                100,
                ['PACKAGE'],
                [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::PICKUP]
            ),
        ]),
        new ExampleCapabilitiesResponse([
            capabilityResult($carrier, 100, ['PACKAGE'], [RefTypesDeliveryTypeV2::STANDARD]),
        ])
    );

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $heavyResult   = $service->createAllCarrierSettings(makeCart('NL', 20001));
    $unknownResult = $service->createAllCarrierSettings(makeCart('NL', 0));
    $carrierId     = FrontendData::getLegacyCarrierIdentifier($carrier);

    expect($heavyResult['carrierSettings'][$carrierId][SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP)])
        ->toBeFalse()
        ->and($unknownResult['carrierSettings'][$carrierId][SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP)])
        ->toBeTrue();
});
