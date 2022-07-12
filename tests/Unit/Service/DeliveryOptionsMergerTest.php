<?php
/** @noinspection StaticClosureCanBeUsedInspection, PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Service;

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierInstabox;

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

it('is an instance of DeliveryOptions', function () {
    expect(DeliveryOptionsMerger::create([new DeliveryOptions()]))->toBeInstanceOf(DeliveryOptions::class);
});

it('merges delivery options', function ($deliveryOptions, $expectation) {
    $result = DeliveryOptionsMerger::create($deliveryOptions);
    expect($result->toArray())->toEqual($expectation);
})->with([
    'a single item' => [
        'deliveryOptions' => [
            [
                'carrier'      => CarrierOptions::CARRIER_POSTNL_NAME,
                'date'         => DEFAULT_DATE,
                'deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            ],
        ],
        'expectation'     => [
            'carrier'         => CarrierOptions::CARRIER_POSTNL_NAME,
            'date'            => DEFAULT_DATE,
            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            'packageType'     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'shipmentOptions' => $emptyShipmentOptions,
            'pickupLocation'  => null,
        ],
    ],

    'with two items' => [
        'deliveryOptions' => [
            [
                'carrier'         => CarrierOptions::CARRIER_POSTNL_NAME,
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
                'carrier'         => CarrierOptions::CARRIER_POSTNL_NAME,
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
            'carrier'         => CarrierOptions::CARRIER_POSTNL_NAME,
            'date'            => DEFAULT_DATE,
            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            'packageType'     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'shipmentOptions' => [
                    'signature' => true,
                    'insurance' => 0,
                    'ageCheck'  => false,
                ] + $emptyShipmentOptions,
            'pickupLocation'  => null,
        ],
    ],

    'with two items with pickup' => [
        'deliveryOptions' => [
            [
                'carrier'         => CarrierInstabox::NAME,
                'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                'shipmentOptions' => [
                    'signature' => null,
                    'insurance' => null,
                    'ageCheck'  => null,
                ],
                'pickupLocation'  => new RetailLocation([
                    'cc'              => CountryCodes::CC_NL,
                    'city'            => DEFAULT_CITY,
                    'locationCode'    => DEFAULT_LOCATION_CODE,
                    'locationName'    => DEFAULT_NAME,
                    'number'          => DEFAULT_NUMBER,
                    'postalCode'      => DEFAULT_POSTAL,
                    'retailNetworkId' => DEFAULT_NETWORK_ID,
                    'street'          => DEFAULT_STREET,
                ]),
            ],
            [
                'carrier'         => CarrierInstabox::NAME,
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
            'carrier'         => CarrierOptions::CARRIER_INSTABOX_NAME,
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
        ],
    ],
]);
