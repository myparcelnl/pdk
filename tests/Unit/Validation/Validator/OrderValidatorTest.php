<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Validation\Validator;

use BadMethodCallException;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use MyParcelNL\Pdk\Validation\Validator\OrderValidator;
use RuntimeException;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesEachMockPdkInstance());

/**
 * array_merge overwrites entire keys, array_merge_recursive only adds to them, thereby corrupting the array.
 * This function only overwrites the keys within the keys that are present, and leaves the rest as is.
 *
 * @param  array ...$arrays
 *
 * @return array
 */
function arrayMergeOrder(array ...$arrays): array
{
    if (! isset($arrays[0])) {
        return [];
    }

    return Arr::undot(
        array_reduce($arrays, static function (array $carry, array $merge) {
            foreach (Arr::dot($merge) as $key => $value) {
                $carry[$key] = $value;
            }
            return $carry;
        }, [])
    );
}

$defaultOrderData = [
    'externalIdentifier' => '1',
    'recipient'          => [
        'cc'         => 'NL',
        'city'       => 'Hoofddorp',
        'postalCode' => '2132JE',
        'street'     => 'Antareslaan 31',
        'number'     => '31',
        'person'     => 'Mike Parcel',
        'email'      => 'test@myparcel.nl',
        'phone'      => '0619438574',
    ],
    'sender'             => [
        'cc'         => 'NL',
        'city'       => 'Hoofddorp',
        'postalCode' => '2132JE',
        'street'     => 'Antareslaan 31',
        'number'     => '31',
        'person'     => 'Mike Parcel',
        'email'      => 'test@myparcel.nl',
        'phone'      => '0619438574',
    ],
    'deliveryOptions'    => [
        'date'         => '2022-02-02',
        'deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
        'packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
    ],
    'physicalProperties' => [
        'weight' => 1000,
    ],
    'shipments'          => null,
];

$createOrder = function (array $input = []) use ($defaultOrderData): PdkOrder {
    return new PdkOrder(arrayMergeOrder($defaultOrderData, $input));
};

it('returns correct schema', function (array $order) use ($createOrder) {
    $validator = $createOrder($order)->getValidator();

    assertMatchesJsonSnapshot(json_encode($validator->getSchema()));
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

    assertMatchesJsonSnapshot(json_encode($errors));
})->with([
        'instabox to France'                          => [
            'order' => [
                'recipient'       => [
                    'cc' => 'FR',
                ],
                'deliveryOptions' => [
                    'carrier' => 'instabox',
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
            'order' => (
            [
                'recipient' => [
                    'cc' => null,
                ],
            ]
            ),
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
                'recipient'       => ['cc' => 'FR'],
                'deliveryOptions' => ['shipmentOptions' => ['insurance' => null]],
            ],
        ],
        'EU package with no weight'                   => [
            'order' => [
                'recipient'          => ['cc' => 'FR'],
                'deliveryOptions'    => ['shipmentOptions' => ['insurance' => 50000]],
                'physicalProperties' => ['weight' => 0],
            ],
        ],
        'EU package with correct insurance'           => [
            'order' => [
                'recipient'       => ['cc' => 'FR'],
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
                'recipient'       => [
                    'cc' => 'BE',
                ],
                'deliveryOptions' => [
                    'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                ],
            ],
        ],
        'ROW package without weight, without invoice' => [
            'order' => [
                'recipient'       => [
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
                'recipient'          => ['cc' => 'US'],
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
                    'carrier'      => CarrierOptions::CARRIER_POSTNL_NAME,
                    'packageType'  => 'package',
                    'labelAmount'  => 2,
                    'deliveryDate' => '2022-12-12 00:00:00',
                ],
                'recipient'          => [
                    'cc'         => CountryCodes::CC_NL,
                    'street'     => 'Pietjestraat',
                    'number'     => '44',
                    'postalCode' => '2901AB',
                    'city'       => 'Amstelveen',
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
                    'carrier'      => CarrierOptions::CARRIER_POSTNL_NAME,
                    'packageType'  => 'package',
                    'labelAmount'  => 2,
                    'deliveryDate' => '2022-12-12 00:00:00',
                ],
                'recipient'          => [
                    'cc'         => CountryCodes::CC_US,
                    'street'     => 'Broadway',
                    'number'     => '1',
                    'postalCode' => '12345',
                    'city'       => 'New York',
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
                    'carrier'     => CarrierOptions::CARRIER_POSTNL_NAME,
                    'packageType' => 'mailbox',
                    'labelAmount' => 1,
                ],
                'recipient'          => [
                    'cc'         => CountryCodes::CC_NL,
                    'street'     => 'P.C. Hooftstraat',
                    'number'     => '1',
                    'postalCode' => '2243AA',
                    'city'       => 'Amsterdam',
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
                    'carrier'     => CarrierOptions::CARRIER_POSTNL_NAME,
                    'packageType' => 'mailbox',
                    'labelAmount' => 1,
                ],
                'recipient'          => [
                    'cc'         => CountryCodes::CC_NL,
                    'street'     => 'P.C. Hooftstraat',
                    'number'     => '1',
                    'postalCode' => '2243AA',
                    'city'       => 'Amsterdam',
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

it('throws an error when calling a nonexistent function', function () use ($createOrder) {
    $order     = $createOrder();
    $validator = $order->getValidator();

    /** @noinspection PhpUndefinedMethodInspection */
    $validator->canHavePietje();
})->throws(BadMethodCallException::class);

it('throws error when trying to validate without an order', function () {
    $validator = Pdk::get(OrderValidator::class);

    $validator->validate();
})->throws(RuntimeException::class);
