<?php
/** @noinspection PhpUndefinedMethodInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use MyParcelNL\Pdk\Shipment\Concern\EncodesRecipient;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use function MyParcelNL\Pdk\Tests\factory;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use RuntimeException;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('can hold and expose data', function () {
    $shipment = new Shipment([
        'carrier'         => factory(Carrier::class)->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)->make(),
        'sender'          => new Address(),
        'recipient'       => new Address(),
        'deliveryOptions' => new DeliveryOptions(),
    ]);

    expect($shipment->getCarrier())
        ->toBeInstanceOf(Carrier::class)
        ->and($shipment->getRecipient())
        ->toBeInstanceOf(Address::class)
        ->and($shipment->getSender())
        ->toBeInstanceOf(Address::class)
        ->and($shipment->getDeliveryOptions())
        ->toBeInstanceOf(DeliveryOptions::class);
});

it('passes carrier to delivery options', function (string $carrierName) {
    $carrier = factory(Carrier::class)->withCarrier($carrierName)->make();
    $shipment = new Shipment([
        'carrier'         => $carrier,
        'deliveryOptions' => new DeliveryOptions([
            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
            'shipmentOptions' => [
                'signature' => true,
            ],
        ]),
    ]);

    $deliveryOptions = $shipment->deliveryOptions;
    expect($deliveryOptions ? $deliveryOptions->carrier->carrier : null)->toEqual($carrierName);
})->with('carrierNames');

// HasCarrierAttribute: when carrier attribute is unset, returns the shop's default carrier.
it('resolves to shop default carrier when carrier attribute is unset', function () {
    // UsesAccountMock sets up a shop with all carriers. Mutate its defaultCarrier.
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $repo */
    $repo    = Pdk::get(PdkAccountRepositoryInterface::class);
    $account = $repo->getAccount();
    $account->shops->first()->defaultCarrier = RefCapabilitiesSharedCarrierV2::POSTNL;
    $repo->store($account);

    $shipment = new Shipment([]);

    expect($shipment->carrier)
        ->toBeInstanceOf(Carrier::class)
        ->and($shipment->carrier->carrier)
        ->toBe(RefCapabilitiesSharedCarrierV2::POSTNL);
});

// HasCarrierAttribute: when carrier attribute is unset and shop has no default, throws RuntimeException.
it('throws RuntimeException when carrier attribute is unset and shop has no default', function () {
    // Explicitly clear the defaultCarrier that ShopFactory sets by default.
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $repo */
    $repo    = Pdk::get(PdkAccountRepositoryInterface::class);
    $account = $repo->getAccount();
    $account->shops->first()->defaultCarrier = null;
    $repo->store($account);

    $shipment = new Shipment([]);

    // Access the carrier getter — this triggers the fallback path which must throw.
    $shipment->carrier;
})->throws(RuntimeException::class, 'No default carrier available');

// HasCarrierAttribute: when carrier attribute is set explicitly, that value wins; shop is not consulted.
it('uses explicitly set carrier without reading shop default', function () {
    // Shop has no default carrier — if HasCarrierAttribute reads it, a RuntimeException would be thrown.
    $carrier  = factory(Carrier::class)->withCarrier(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU)->make();
    $shipment = new Shipment(['carrier' => $carrier]);

    expect($shipment->carrier->carrier)->toBe(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU);
});

it('encodes recipient street fields correctly', function () {
    $trait = new class {
        use EncodesRecipient;
        public function publicEncodeRecipient($recipient) {
            return $this->encodeRecipient($recipient);
        }
    };

    // Test: long street, empty extra field
    $recipient = factory(ContactDetails::class)
        ->withStreet('Bldg. 3, #81, Lane 1159, East Kangqiao Rd.')
        ->withStreetAdditionalInfo(null)
        ->make();
    $result = $trait->publicEncodeRecipient($recipient);
    expect($result['street'])->toBe('Bldg. 3, #81, Lane 1159, East Kangqiao R')
        ->and($result['street_additional_info'])->toBe('d.');

    // Test: overlap between street and extra field
    $recipient = factory(ContactDetails::class)
        ->withStreet('Bldg. 3, #81, Lane 1159, East Kangqiao Rd.')
        ->withStreetAdditionalInfo('East Kangqiao Rd.')
        ->make();
    $result = $trait->publicEncodeRecipient($recipient);
    expect($result['street'])->not()->toContain('East Kangqiao Rd.')
        ->and($result['street_additional_info'])->toBe('East Kangqiao Rd.');

    // Test: extra field filled, no overlap
    $recipient = factory(ContactDetails::class)
        ->withStreet('Bldg. 3, #81, Lane 1159, East Kangqiao Rd.')
        ->withStreetAdditionalInfo('Apt. 5B')
        ->make();
    $result = $trait->publicEncodeRecipient($recipient);
    expect($result['street'])->toBe('Bldg. 3, #81, Lane 1159, East Kangqiao R')
        ->and($result['street_additional_info'])->toBe('Apt. 5B');
});
