<?php
/** @noinspection PhpUndefinedMethodInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use MyParcelNL\Pdk\Shipment\Concern\EncodesRecipient;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use function MyParcelNL\Pdk\Tests\factory;

usesShared(new UsesMockPdkInstance());

it('can hold and expose data', function () {
    $shipment = new Shipment([
        'carrier'         => new Carrier(['carrier' => ['name' => Carrier::CARRIER_POSTNL_NAME]]),
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
    $shipment = new Shipment([
        'carrier'         => new Carrier(['name' => $carrierName]),
        'deliveryOptions' => new DeliveryOptions([
            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
            'shipmentOptions' => [
                'signature' => true,
            ],
        ]),
    ]);

    $deliveryOptions = $shipment->deliveryOptions;
    expect($deliveryOptions ? $deliveryOptions->carrier->name : null)->toEqual($carrierName);
})->with('carrierNames');

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
