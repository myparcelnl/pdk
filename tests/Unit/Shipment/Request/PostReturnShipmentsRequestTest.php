<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesNotificationsMock;
use RuntimeException;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;
use function MyParcelNL\Pdk\Tests\usesShared;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;

usesShared(new UsesMockPdkInstance(), new UsesNotificationsMock(), new UsesAccountMock());

it('creates a valid request for return shipments', function () {
    $shipmentCollection = new ShipmentCollection([
        new Shipment([
            'id' => 100020,
            'carrier' => factory(Carrier::class)->withCarrier('POSTNL')->make(),
            'referenceIdentifier' => 'REF-1',
            'recipient' => new ContactDetails([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'address1' => 'Main Street',
                'number' => '1',
                'postalCode' => '1234AB',
                'city' => 'Amsterdam',
                'cc' => 'NL',
                'email' => 'john@example.com',
                'person' => 'John Doe',
            ]),
            'deliveryOptions' => [
                'packageType' => 1,
            ],
        ]),
    ]);

    $request = new PostReturnShipmentsRequest($shipmentCollection);

    $body = json_decode($request->getBody(), true);

    expect($request->getMethod())
        ->toBe('POST')
        ->and($request->getPath())
        ->toBe('/shipments')
        ->and($body)
        ->toBeArray()
        ->and(Arr::get($body, 'data.return_shipments.0.parent'))
        ->toBe(100020)
        ->and(Arr::get($body, 'data.return_shipments.0.reference_identifier'))
        ->toBe('REF-1');
});

it('throws an exception when recipient data is missing', function () {
    $shipmentCollection = new ShipmentCollection([
        new Shipment([
            'id' => 100020,
            'carrier' => factory(Carrier::class)->withCarrier('POSTNL')->make(),
            'referenceIdentifier' => 'REF-1',
            // No recipient data
        ]),
    ]);

    $request = new PostReturnShipmentsRequest($shipmentCollection);

    // This should throw an exception
    $request->getBody();
})->throws(RuntimeException::class, 'Recipient data is required for return shipments');

it('ensures return capabilities for shipments', function () {
    $shipmentCollection = new ShipmentCollection([
        new Shipment([
            'id' => 100020,
            'carrier' => factory(Carrier::class)->withCarrier('POSTNL')->make(),
            'referenceIdentifier' => 'REF-1',
            'recipient' => new ContactDetails([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'address1' => 'Main Street',
                'number' => '1',
                'postalCode' => '1234AB',
                'city' => 'Amsterdam',
                'cc' => 'NL',
                'email' => 'john@example.com',
                'person' => 'John Doe',
            ]),
            'deliveryOptions' => [
                'packageType' => 1,
            ],
            'isReturn' => false,
        ]),
    ]);

    $request = new PostReturnShipmentsRequest($shipmentCollection);
    $body = json_decode($request->getBody(), true);

    // Check if the shipment is properly formatted for return
    expect(Arr::get($body, 'data.return_shipments.0.options.package_type'))
        ->toBe(1) // Default package type for return shipments
        ->and(Arr::get($body, 'data.return_shipments.0.sender.cc'))
        ->toBe('NL')
        ->and(Arr::get($body, 'data.return_shipments.0.sender.person'))
        ->toBe('John Doe');
});

/**
 * Build a one-shipment collection for return-capabilities tests.
 * The carrier is PostNL by default; the recipient has cc=NL to satisfy the country-code guard.
 */
function makeReturnShipmentCollection(string $carrierName = RefCapabilitiesSharedCarrierV2::POSTNL): ShipmentCollection
{
    return new ShipmentCollection([
        new Shipment([
            'id'                  => 999,
            'carrier'             => factory(Carrier::class)->withCarrier($carrierName)->make(),
            'referenceIdentifier' => 'REF-TEST',
            'recipient'           => new ContactDetails([
                'cc'     => 'NL',
                'email'  => 'test@example.com',
                'person' => 'Test Person',
            ]),
            'deliveryOptions' => ['packageType' => 1],
        ]),
    ]);
}

// Site 3 case 1: carrier supports returns — no fallback path, no notification emitted.
it('keeps the original carrier when it supports returns', function () {
    $supportsReturns = new class(Pdk::get(CarrierCapabilitiesRepository::class)) extends CapabilitiesValidationService {
        public function supportsReturns(Carrier $carrier, string $countryCode): bool { return true; }
    };
    mockPdkProperties([CapabilitiesValidationService::class => $supportsReturns]);

    $request = new PostReturnShipmentsRequest(makeReturnShipmentCollection(RefCapabilitiesSharedCarrierV2::POSTNL));
    $body    = json_decode($request->getBody(), true);

    expect(Notifications::isEmpty())->toBeTrue()
        ->and(Arr::get($body, 'data.return_shipments.0.carrier'))
        ->not()->toBeNull();
});

// Site 3 case 2: carrier does NOT support returns AND shop has a default → swap + notification.
it('swaps to default carrier and emits a notification when the carrier lacks return support and shop has a default', function () {
    // Set the shop's defaultCarrier while keeping all carriers in the repository.
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $repo */
    $repo    = Pdk::get(PdkAccountRepositoryInterface::class);
    $account = $repo->getAccount();
    $account->shops->first()->defaultCarrier = RefCapabilitiesSharedCarrierV2::POSTNL;
    $repo->store($account);

    $noReturns = new class(Pdk::get(CarrierCapabilitiesRepository::class)) extends CapabilitiesValidationService {
        public function supportsReturns(Carrier $carrier, string $countryCode): bool { return false; }
    };
    mockPdkProperties([CapabilitiesValidationService::class => $noReturns]);

    $request = new PostReturnShipmentsRequest(makeReturnShipmentCollection(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU));
    $body    = json_decode($request->getBody(), true);

    // Notification must have been emitted referencing the default carrier.
    expect(Notifications::isEmpty())->toBeFalse();

    $notification = Notifications::all()->first();
    // content is stored as an array; the first element is the full message string.
    expect($notification->content[0])->toContain(RefCapabilitiesSharedCarrierV2::POSTNL);

    // The encoded carrier is the default (PostNL id).
    expect(Arr::get($body, 'data.return_shipments.0.carrier'))->not()->toBeNull();
});

// Site 3 case 3: carrier does NOT support returns AND shop has NO default → keep original carrier, no notification.
//
// Deliberate semantic change: without a known fallback, we keep the user's carrier so the downstream
// export attempt surfaces a meaningful error rather than silently swapping to an unknown carrier.
it('keeps the original carrier and emits no notification when the carrier lacks return support and shop has no default', function () {
    // Explicitly clear the defaultCarrier that ShopFactory sets by default.
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $repo */
    $repo    = Pdk::get(PdkAccountRepositoryInterface::class);
    $account = $repo->getAccount();
    $account->shops->first()->defaultCarrier = null;
    $repo->store($account);

    $noReturns = new class(Pdk::get(CarrierCapabilitiesRepository::class)) extends CapabilitiesValidationService {
        public function supportsReturns(Carrier $carrier, string $countryCode): bool { return false; }
    };
    mockPdkProperties([CapabilitiesValidationService::class => $noReturns]);

    $request = new PostReturnShipmentsRequest(makeReturnShipmentCollection(RefCapabilitiesSharedCarrierV2::POSTNL));
    $body    = json_decode($request->getBody(), true);

    $postnlId = Utils::convertToId(RefCapabilitiesSharedCarrierV2::POSTNL, Carrier::CARRIER_NAME_ID_MAP);

    expect(Notifications::isEmpty())->toBeTrue()
        ->and(Arr::get($body, 'data.return_shipments.0.carrier'))->toBe($postnlId);
});

