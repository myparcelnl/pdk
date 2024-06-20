<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Validation\Validator\OrderValidator;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPlatform;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

/**
 * From Laravel 8
 *
 * @param  array $dotted
 *
 * @return array
 */
function arrayUndot(array $dotted): array
{
    $set   = static function (&$array, $key, $value): array {
        if (null === $key) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;

        return $array;
    };
    $array = [];
    foreach ($dotted as $key => $value) {
        $set($array, $key, $value);
    }
    return $array;
}

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

    $dotted = array_reduce($arrays, static function (array $carry, array $merge) {
        foreach (Arr::dot($merge) as $key => $value) {
            $carry[$key] = $value;
        }
        return $carry;
    }, []);
    return arrayUndot($dotted);
}

const STANDARD_INPUT = [
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
        'date'         => '2077-10-23 09:47:51',
        'deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
        'packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
    ],
    'physicalProperties' => [
        'weight' => 1000,
    ],
    'shipments'          => null,
];

usesShared(new UsesMockPdkInstance());

it('returns correct schema', function (string $platform, string $carrier, string $packageType, string $expectedSchema) {
    $reset = mockPlatform($platform);

    $pdkOrder = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType($packageType)
        )
        ->make();

    /** @var \MyParcelNL\Pdk\Validation\Validator\OrderValidator $validator */
    $validator = Pdk::get(OrderValidator::class);
    $validator->setOrder($pdkOrder);

    $schema = $validator->getSchema();

    expect($schema['description'])->toBe($expectedSchema);

    $reset();
})->with([
    'myparcel, postnl, package' => [
        Platform::MYPARCEL_NAME,
        Carrier::CARRIER_POSTNL_NAME,
        DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        'myparcel/order/postnl/nl_package',
    ],

    'myparcel, postnl, mailbox' => [
        Platform::MYPARCEL_NAME,
        Carrier::CARRIER_POSTNL_NAME,
        DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        'myparcel/order/postnl/mailbox',
    ],
]);

it('validates order', function (array $input) {
    $pdkOrder = new PdkOrder($input);

    /** @var PdkOrderOptionsServiceInterface $orderOptionsService */
    $orderOptionsService = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder            = $orderOptionsService->calculateShipmentOptions($pdkOrder);

    /** @var \MyParcelNL\Pdk\Validation\Validator\OrderValidator $validator */
    $validator = Pdk::get(OrderValidator::class);
    $validator->setOrder($newOrder);

    $isValid = $validator->validate();

    $errors = Arr::dot($validator->getErrors());
    expect($isValid)->toBe(empty($errors));

    if (! empty($errors)) {
        assertMatchesJsonSnapshot(json_encode($errors));
    }
})->with([
        'dhlforyou to France'                         => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'recipient'       => [
                        'cc' => 'FR',
                    ],
                    'deliveryOptions' => [
                        'carrier' => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                    ],
                ]
            ),
        ],
        'postnl non-standard delivery without date'   => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'deliveryOptions' => [
                        'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                        'date'            => null,
                        'shipmentOptions' => ['signature' => true, 'only_recipient' => true],
                    ],
                ]
            ),
        ],
        'pickup without pickupLocation'               => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'deliveryOptions' => [
                        'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                        'shipmentOptions' => [
                            'signature' => true,
                        ],
                    ],
                ]
            ),
        ],
        'pickup without location code'                => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
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
                ]
            ),
        ],
        'package without country'                     => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'recipient' => [
                        'cc' => null,
                    ],
                ]
            ),
        ],
        'postnl with same day delivery'               => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                ['deliveryOptions' => ['shipmentOptions' => ['sameDayDelivery' => true]]]
            ),
        ],
        'morning delivery with age check'             => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'deliveryOptions' => [
                        'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                        'shipmentOptions' => ['ageCheck' => true],
                    ],
                ]
            ),
        ],
        'weight of 25kg'                              => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'physicalProperties' => ['weight' => 25000],
                ]
            ),
        ],
        'weight of 29kg with large_format'            => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'physicalProperties' => ['weight' => 29999],
                ],
                [
                    'deliveryOptions' => [
                        'shipmentOptions' => [
                            'largeFormat' => true,
                        ],
                    ],
                ]
            ),
        ],
        'mailbox with morning delivery'               => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'deliveryOptions' => [
                        'deliveryType' => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                        'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                    ],
                ]
            ),
        ],
        'EU package without insurance'                => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'recipient'       => [
                        'cc' => 'FR',
                    ],
                    'deliveryOptions' => ['shipmentOptions' => ['insurance' => null]],
                ]
            ),
        ],
        'EU package with no weight'                   => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'recipient'          => [
                        'cc' => 'FR',
                    ],
                    'deliveryOptions'    => ['shipmentOptions' => ['insurance' => 50000]],
                    'physicalProperties' => ['weight' => 0],
                ]
            ),
        ],
        'EU package with correct insurance'           => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'recipient'       => [
                        'cc' => 'FR',
                    ],
                    'deliveryOptions' => ['shipmentOptions' => ['insurance' => 50000]],
                ]
            ),
        ],
        'mailbox with shipmentOptions'                => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'deliveryOptions' => [
                        'packageType'     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        'shipmentOptions' => [
                            'signature' => true,
                        ],
                    ],
                ]
            ),
        ],
        'BE mailbox'                                  => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'recipient'       => [
                        'cc' => 'BE',
                    ],
                    'deliveryOptions' => [
                        'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                    ],
                ]
            ),
        ],
        'ROW package without weight, without invoice' => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'recipient'       => [
                        'cc' => 'US',
                    ],
                    'deliveryOptions' => [
                        'shipmentOptions' => [
                            'insurance' => 20000,
                        ],
                    ],
                ]
            ),
        ],
        'ROW package'                                 => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'recipient'          => [
                        'cc' => 'US',
                    ],
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
                ]
            ),
        ],
        'Allows property tracked'   => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'deliveryOptions' => [
                        'shipmentOptions' => [
                            'tracked' => 1,
                        ]
                    ]
                ]
            )
        ],
        'Small packets not NL tracked' => [
            'input' => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'deliveryOptions' => [
                        'packageType' => DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
                        'shipmentOptions' => [
                            'tracked' => 0,
                        ]
                    ],
                    'recipient'          => [
                        'cc' => 'FR',
                    ]
                ]
            )
        ]
    ]
);

