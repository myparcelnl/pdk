<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
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
    int $weightMax = 23000
): array {
    return [
        'carrier'            => $carrier,
        'contract'           => ['id' => $contractId, 'type' => 'MAIN'],
        'packageTypes'       => $packageTypes,
        'options'            => (object) [],
        'physicalProperties' => [
            'weight' => [
                'min' => ['value' => $weightMin, 'unit' => 'g'],
                'max' => ['value' => $weightMax, 'unit' => 'g'],
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
function makeCart(string $cc, int $weight = 1000): PdkCart
{
    return new PdkCart([
        'shippingMethod' => [
            'shippingAddress' => ['cc' => $cc],
        ],
        'lines' => [
            [
                'quantity' => 1,
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
