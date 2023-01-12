<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Fulfilment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Model\Shipment as PdkShipment;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::create());
});

it('creates fulfilment shipment from pdk shipment', function (array $input) {
    $pdkShipment = new PdkShipment($input);
    $shipment    = Shipment::fromPdkShipment($pdkShipment);

    expect($shipment)->toBeInstanceOf(Shipment::class);
    assertMatchesJsonSnapshot(json_encode($shipment->toArray()));
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
                'id'             => 1,
                'subscriptionId' => '1234567890',
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
                'fullStreet' => 'Antareslaan 31',
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
    assertMatchesJsonSnapshot(json_encode($shipment->toArray()));
});