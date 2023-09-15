<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use RuntimeException;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesEachMockPdkInstance());

const DEFAULT_ORDER_DATA = [
    'externalIdentifier' => '1',
    'shippingAddress'    => [
        'cc'         => 'NL',
        'city'       => 'Hoofddorp',
        'postalCode' => '2132JE',
        'person'     => 'Mike Parcel',
        'email'      => 'test@myparcel.nl',
        'phone'      => '0619438574',
        'address1'   => 'Antareslaan 31',
    ],
    'senderAddress'      => [
        'cc'         => 'NL',
        'city'       => 'Hoofddorp',
        'postalCode' => '2132JE',
        'person'     => 'Mike Parcel',
        'email'      => 'test@myparcel.nl',
        'phone'      => '0619438574',
        'address1'   => 'Antareslaan 31',
    ],
    'deliveryOptions'    => [
        'date'         => '2077-10-23 09:47:51',
        'deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
        'packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
    ],
    'physicalProperties' => [
        'weight' => 1000,
    ],
    'shipments'          => null,
];

$createOrder = fn(array $input = []): PdkOrder => new PdkOrder(array_replace_recursive(DEFAULT_ORDER_DATA, $input));

it('returns correct schema', function (array $order) use ($createOrder) {
    $validator = $createOrder($order)->getValidator();

    assertMatchesJsonSnapshot(json_encode($validator->getSchema(), JSON_THROW_ON_ERROR));
})->with([
    'pickup without location code' => [
        'order' => [
            'deliveryOptions' => [
                'deliveryType'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                'pickupLocation' => [
                    'state' => 'Drenthe',
                ],
            ],
        ],
    ],
]);

it('validates order', function (array $order) use ($createOrder) {
    $validator = $createOrder($order)->getValidator();

    $isValid = $validator->validate();
    $errors  = $validator->getErrors();

    expect($isValid)
        ->toBe(empty($errors));

    assertMatchesJsonSnapshot(json_encode($errors, JSON_THROW_ON_ERROR));
})->with([
        'dhlforyou to France'                         => [
            'order' => [
                'shippingAddress' => [
                    'cc' => 'FR',
                ],
                'deliveryOptions' => [
                    'carrier' => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                ],
            ],
        ],
        'postnl non-standard delivery without date'   => [
            'order' => [
                'deliveryOptions' => [
                    'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                    'date'            => null,
                    'shipmentOptions' => ['signature' => true, 'only_recipient' => true],
                ],
            ],
        ],
        'pickup without pickupLocation'               => [
            'order' => [
                'deliveryOptions' => [
                    'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                    'date'            => '2077-10-23 09:47:51',
                    'shipmentOptions' => [
                        'signature' => true,
                    ],
                ],
            ],
        ],
        'pickup without location code'                => [
            'order' => [
                'deliveryOptions' => [
                    'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                    'date'            => '2077-10-23 09:47:51',
                    'shipmentOptions' => [
                        'signature'     => true,
                        'onlyRecipient' => true,
                    ],
                    'pickupLocation'  => [
                        'state' => 'Drenthe',
                    ],
                ],
            ],
        ],
        'package without country'                     => [
            'order' => [
                'shippingAddress' => [
                    'cc' => null,
                ],
            ],
        ],
        'postnl with same day delivery'               => [
            'order' => ['deliveryOptions' => ['shipmentOptions' => ['sameDayDelivery' => true]]],
        ],
        'morning delivery with age check'             => [
            'order' => [
                'deliveryOptions' => [
                    'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                    'shipmentOptions' => ['ageCheck' => true],
                ],
            ],
        ],
        'weight of 25kg'                              => [
            'order' => [
                'physicalProperties' => ['weight' => 25000],
            ],
        ],
        'weight of 29kg with large_format'            => [
            'order' => [
                'physicalProperties' => ['weight' => 29999],
                'deliveryOptions'    => [
                    'shipmentOptions' => [
                        'largeFormat' => true,
                    ],
                ],
            ],
        ],
        'mailbox with morning delivery'               => [
            'order' => [
                'deliveryOptions' => [
                    'deliveryType' => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                    'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                ],
            ],
        ],
        'EU package without insurance'                => [
            'order' => [
                'shippingAddress' => ['cc' => 'FR'],
                'deliveryOptions' => ['shipmentOptions' => ['insurance' => null]],
            ],
        ],
        'EU package with no weight'                   => [
            'order' => [
                'shippingAddress'    => ['cc' => 'FR'],
                'deliveryOptions'    => ['shipmentOptions' => ['insurance' => 50000]],
                'physicalProperties' => ['weight' => 0],
            ],
        ],
        'EU package with correct insurance'           => [
            'order' => [
                'shippingAddress' => ['cc' => 'FR'],
                'deliveryOptions' => ['shipmentOptions' => ['insurance' => 50000]],
            ],
        ],
        'mailbox with shipmentOptions'                => [
            'order' => [
                'deliveryOptions' => [
                    'packageType'     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                    'shipmentOptions' => [
                        'signature' => true,
                    ],
                ],
            ],
        ],
        'BE mailbox'                                  => [
            'order' => [
                'shippingAddress' => [
                    'cc' => 'BE',
                ],
                'deliveryOptions' => [
                    'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                ],
            ],
        ],
        'ROW package without weight, without invoice' => [
            'order' => [
                'shippingAddress' => [
                    'cc' => 'US',
                ],
                'deliveryOptions' => [
                    'shipmentOptions' => [
                        'insurance' => 20000,
                    ],
                ],
            ],
        ],
        'ROW package'                                 => [
            'order' => [
                'shippingAddress'    => ['cc' => 'US'],
                'customsDeclaration' => [
                    'invoice' => '1',
                    'items'   => [
                        [
                            'amount' => 1,
                            'weight' => 1000,
                        ],
                    ],
                ],
                'deliveryOptions'    => [
                    'shipmentOptions' => [
                        'insurance' => 20000,
                    ],
                ],
            ],
        ],
    ]
);

it('tests attributes on PdkOrders', function (array $order, string $method, $input, $output) use ($createOrder) {
    $orderValidator = $createOrder($order)->getValidator();

    expect($orderValidator->{$method}($input))
        ->toBe($output);
})->with(
    [
        'check delivery date'              => [
            'order'  => [
                'externalIdentifier' => '245',
                'physicalProperties' => [
                    'weight' => 20,
                ],
                'deliveryOptions'    => [
                    'carrier'      => Carrier::CARRIER_POSTNL_NAME,
                    'packageType'  => 'package',
                    'labelAmount'  => 2,
                    'deliveryDate' => '2022-12-12 00:00:00',
                ],
                'shippingAddress'    => [
                    'cc'         => CountryCodes::CC_NL,
                    'postalCode' => '2901AB',
                    'city'       => 'Amstelveen',
                    'address1'   => 'Pietjestraat 44',
                ],
            ],
            'method' => 'canHaveDate',
            'input'  => '2022-12-12 00:00:00',
            'output' => true,
        ],
        'check weight'                     => [
            'order'  => [
                'externalIdentifier' => '245',
                'physicalProperties' => [
                    'weight' => 20,
                ],
                'deliveryOptions'    => [
                    'carrier'      => Carrier::CARRIER_POSTNL_NAME,
                    'packageType'  => 'package',
                    'labelAmount'  => 2,
                    'deliveryDate' => '2022-12-12 00:00:00',
                ],
                'shippingAddress'    => [
                    'cc'         => CountryCodes::CC_US,
                    'postalCode' => '12345',
                    'city'       => 'New York',
                    'address1'   => 'Broadway 1',
                ],
            ],
            'method' => 'canHaveWeight',
            'input'  => 200,
            'output' => true,
        ],
        'check mailbox weight'             => [
            'order'  => [
                'externalIdentifier' => '245',
                'physicalProperties' => [
                    'weight' => 80,
                ],
                'deliveryOptions'    => [
                    'carrier'     => Carrier::CARRIER_POSTNL_NAME,
                    'packageType' => 'mailbox',
                    'labelAmount' => 1,
                ],
                'shippingAddress'    => [
                    'cc'         => CountryCodes::CC_NL,
                    'postalCode' => '2243AA',
                    'city'       => 'Amsterdam',
                    'address1'   => 'P.C. Hooftstraat 1',
                ],
            ],
            'method' => 'canHaveWeight',
            'input'  => 200,
            'output' => true,
        ],
        'check weight with null parameter' => [
            'order'  => [
                'externalIdentifier' => '245',
                'physicalProperties' => [
                    'weight' => 80,
                ],
                'deliveryOptions'    => [
                    'carrier'     => Carrier::CARRIER_POSTNL_NAME,
                    'packageType' => 'mailbox',
                    'labelAmount' => 1,
                ],
                'shippingAddress'    => [
                    'cc'         => CountryCodes::CC_NL,
                    'postalCode' => '2243AA',
                    'city'       => 'Amsterdam',
                    'address1'   => 'P.C. Hooftstraat 1',
                ],
            ],
            'method' => 'canHaveWeight',
            'input'  => null,
            'output' => false,
        ],
        'check signature'                  => [
            'order'  => [
                'carrier' => [
                    'name' => 'postnl',
                ],
            ],
            'method' => 'canHaveSignature',
            'input'  => null,
            'output' => true,
        ],
    ]
);

it('throws error when trying to validate without an order', function () {
    /** @var \MyParcelNL\Pdk\Validation\Validator\OrderValidator $validator */
    $validator = Pdk::get(OrderValidator::class);

    $validator->validate();
})->throws(RuntimeException::class);

it('throws error when trying to get schema without an order', function () {
    /** @var \MyParcelNL\Pdk\Validation\Validator\OrderValidator $validator */
    $validator = Pdk::get(OrderValidator::class);

    $validator->getSchema();
})->throws(RuntimeException::class);

function createValidator(string $platformName, string $carrierName): OrderValidator
{
    MockPdkFactory::create(['platform' => $platformName]);

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($carrierName))
        ->make();

    return $order->getValidator();
}

it('can have age check', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveAgeCheck())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have date', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveDate())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have direct return', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveDirectReturn())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have evening delivery', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveEveningDelivery())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have hide sender', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveHideSender())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have insurance', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveInsurance())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have large format', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveLargeFormat())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have morning delivery', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveMorningDelivery())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have multi collo', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveMultiCollo())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have only recipient', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveOnlyRecipient())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have pickup', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHavePickup())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have same day delivery', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveSameDayDelivery())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have signature', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveSignature())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

it('can have weight', function (string $platformName, string $carrierName, bool $outcome) {
    expect(createValidator($platformName, $carrierName)->canHaveWeight())->toBe($outcome);
})->with([
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::MYPARCEL_NAME, Carrier::CARRIER_DHL_FOR_YOU_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_POSTNL_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_BPOST_NAME, true],
    [Platform::SENDMYPARCEL_NAME, Carrier::CARRIER_DPD_NAME, true],
]);

