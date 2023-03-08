<?php
/** @noinspection StaticClosureCanBeUsedInspection, PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Shipment\Service\DeliveryOptionsMerger;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

const DEFAULT_LOCATION_CODE = '98125';
const DEFAULT_DATE          = '2022-07-22 06:00:00';
const DEFAULT_NAME          = 'MyParcel';
const DEFAULT_CITY          = 'Hoofddorp';
const DEFAULT_NUMBER        = '31';
const DEFAULT_POSTAL        = '2132 JE';
const DEFAULT_STREET        = 'Antareslaan';
const DEFAULT_NETWORK_ID    = '1';

$emptyRetailLocation  = (new RetailLocation())->toArray();
$emptyShipmentOptions = (new ShipmentOptions())->toArray();

usesShared(new UsesMockPdkInstance());
it('is an instance of DeliveryOptions', function () {
    expect(DeliveryOptionsMerger::create([new DeliveryOptions()]))->toBeInstanceOf(DeliveryOptions::class);
});

it('merges delivery options', function ($deliveryOptions, $expectation) {
    $result = DeliveryOptionsMerger::create($deliveryOptions);

    expect($result)
        ->toBeInstanceOf(DeliveryOptions::class)
        ->and($result->toArray())
        ->toEqual($expectation);
})->with([
    'a single item' => [
        'deliveryOptions' => [
            [
                'carrier'      => Carrier::CARRIER_POSTNL_NAME,
                'date'         => DEFAULT_DATE,
                'deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            ],
        ],
        'expectation'     => [
            'carrier'         => Carrier::CARRIER_POSTNL_NAME,
            'date'            => DEFAULT_DATE,
            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            'packageType'     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'shipmentOptions' => $emptyShipmentOptions,
            'pickupLocation'  => null,
            'labelAmount'     => 1,
        ],
    ],

    'with two items' => [
        'deliveryOptions' => [
            [
                'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                'date'            => DEFAULT_DATE,
                'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                'packageType'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                'shipmentOptions' => [
                    'signature' => false,
                    'insurance' => 500,
                    'ageCheck'  => true,
                ],
                'pickupLocation'  => null,
            ],
            [
                'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                'date'            => null,
                'deliveryType'    => null,
                'packageType'     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                'shipmentOptions' => [
                    'signature' => true,
                    'insurance' => 0,
                    'ageCheck'  => false,
                ],
                'pickupLocation'  => null,
            ],
        ],
        'expectation'     => [
            'carrier'         => Carrier::CARRIER_POSTNL_NAME,
            'date'            => DEFAULT_DATE,
            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            'packageType'     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'shipmentOptions' => [
                    'signature' => true,
                    'insurance' => 0,
                    'ageCheck'  => false,
                ] + $emptyShipmentOptions,
            'pickupLocation'  => null,
            'labelAmount'     => 1,
        ],
    ],

    'with two items with pickup' => [
        'deliveryOptions' => [
            [
                'carrier'         => Carrier::CARRIER_INSTABOX_NAME,
                'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                'shipmentOptions' => [
                    'signature' => null,
                    'insurance' => null,
                    'ageCheck'  => null,
                ],
                'pickupLocation'  => [
                    'cc'              => CountryCodes::CC_NL,
                    'city'            => DEFAULT_CITY,
                    'locationCode'    => DEFAULT_LOCATION_CODE,
                    'locationName'    => DEFAULT_NAME,
                    'number'          => DEFAULT_NUMBER,
                    'postalCode'      => DEFAULT_POSTAL,
                    'retailNetworkId' => DEFAULT_NETWORK_ID,
                    'street'          => DEFAULT_STREET,
                ],
            ],
            [
                'carrier'         => Carrier::CARRIER_INSTABOX_NAME,
                'date'            => null,
                'deliveryType'    => null,
                'packageType'     => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                'shipmentOptions' => [
                    'signature' => false,
                    'insurance' => 500,
                    'ageCheck'  => true,
                ],
            ],
        ],
        'expectation'     => [
            'carrier'         => Carrier::CARRIER_INSTABOX_NAME,
            'date'            => null,
            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            'packageType'     => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
            'shipmentOptions' => [
                    'ageCheck'  => true,
                    'insurance' => 500,
                    'signature' => false,
                ] + $emptyShipmentOptions,
            'pickupLocation'  => [
                    'cc'              => CountryCodes::CC_NL,
                    'city'            => DEFAULT_CITY,
                    'locationCode'    => DEFAULT_LOCATION_CODE,
                    'locationName'    => DEFAULT_NAME,
                    'number'          => DEFAULT_NUMBER,
                    'postalCode'      => DEFAULT_POSTAL,
                    'retailNetworkId' => DEFAULT_NETWORK_ID,
                    'street'          => DEFAULT_STREET,
                ] + $emptyRetailLocation,
            'labelAmount'     => 1,
        ],
    ],
]);