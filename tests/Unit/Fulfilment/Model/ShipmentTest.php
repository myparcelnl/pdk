<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\Shipment as PdkShipment;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());
it('creates fulfilment shipment from pdk shipment', function (array $input) {
    $pdkShipment = new PdkShipment($input);
    $shipment    = Shipment::fromPdkShipment($pdkShipment);

    expect($shipment)->toBeInstanceOf(Shipment::class);
    assertMatchesJsonSnapshot(json_encode($shipment->toArrayWithoutNull()));
})->with([
    'empty shipment'            => [[]],
    'shipment with all options' => [
        [
            'deliveryOptions'    => [
                'packageType'    => 'package',
                'deliveryType'   => 'standard',
                'pickupLocation' => [
                    'locationCode' => 34653,
                ],
            ],
            'carrier'            => [
                'id'         => 1,
                'contractId' => '1234567890',
            ],
            'customsDeclaration' => [
                'contents' => '00',
                'invoice'  => 'ABC123456',
                'items'    => [
                    [
                        'amount'         => 10,
                        'classification' => '12345',
                        'country'        => 'NL',
                        'description'    => 'A word',
                        'itemValue'      => [
                            'amount'   => 100,
                            'currency' => 'EUR',
                        ],
                        'weight'         => 100,
                    ],
                ],
                'weight'   => 1000,
            ],
            'recipient'          => [
                'cc'         => 'NL',
                'city'       => 'Hoofddorp',
                'address1'   => 'Antareslaan 31',
                'postalCode' => '2132JE',
                'company'    => 'MyParcel',
                'email'      => 'test@myparcel.nl',
                'person'     => 'Felicia Parcel',
                'phone'      => '0612345678',
            ],
        ],
    ],
]);

it('returns empty fulfilment shipment when no pdk shipment is passed', function () {
    $shipment = Shipment::fromPdkShipment(null);
    expect($shipment)->toBeInstanceOf(Shipment::class);
    assertMatchesJsonSnapshot(json_encode($shipment->toArrayWithoutNull()));
});

// Site 4: when carrier is not pre-set and shop has a default, the raw V2 string is stored on the attribute.
it('uses shop default carrier V2 string when carrier is not pre-set', function () {
    // Set the shop's defaultCarrier while keeping all carriers in the repository.
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $repo */
    $repo    = Pdk::get(PdkAccountRepositoryInterface::class);
    $account = $repo->getAccount();
    $account->shops->first()->defaultCarrier = RefCapabilitiesSharedCarrierV2::POSTNL;
    $repo->store($account);

    $shipment = new Shipment();

    // The raw attribute holds the V2 string directly (no legacy-id round-trip).
    expect($shipment->attributes['carrier'])->toBe(RefCapabilitiesSharedCarrierV2::POSTNL);
});

// Site 4: when carrier is pre-set, it wins; shop default is not consulted.
it('keeps pre-set carrier attribute when one is supplied', function () {
    // defaultCarrier is set — but since carrier is explicitly provided, the shop must not be consulted.
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $repo */
    $repo    = Pdk::get(PdkAccountRepositoryInterface::class);
    $account = $repo->getAccount();
    $account->shops->first()->defaultCarrier = RefCapabilitiesSharedCarrierV2::POSTNL;
    $repo->store($account);

    $carrier  = factory(Carrier::class)->withCarrier(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU)->make();
    $shipment = new Shipment(['carrier' => $carrier]);

    expect($shipment->attributes['carrier'])->toBe(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU);
});

// Site 4: when carrier is not pre-set and shop has no default, the raw attribute remains null (no throw).
it('leaves carrier attribute null when carrier is not pre-set and shop has no default', function () {
    // Explicitly clear the defaultCarrier that ShopFactory sets by default.
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $repo */
    $repo    = Pdk::get(PdkAccountRepositoryInterface::class);
    $account = $repo->getAccount();
    $account->shops->first()->defaultCarrier = null;
    $repo->store($account);

    $shipment = new Shipment();

    expect($shipment->attributes['carrier'])->toBeNull();
});
