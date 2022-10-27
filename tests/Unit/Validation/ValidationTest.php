<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Validation\OrderValidator;
use MyParcelNL\Sdk\src\Support\Arr;

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
        'date'         => '2022-02-02',
        'deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
        'packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
    ],
    'physicalProperties' => [
        'weight' => 1000,
    ],
    'shipments'          => null,
];

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::create());
});

it('returns correct schema', function ($input, $output) {
    $pdkOrder  = new PdkOrder($input);
    $validator = new OrderValidator($pdkOrder);

    $val   = $validator->getValidationSchema();
    $order = $pdkOrder->toArray(); //debug

    expect(Arr::dot($val))
        ->toBe($output);
})->with([
    'pickup without location code' => [
        'input'  => arrayMergeOrder(
            STANDARD_INPUT,
            [
                'deliveryOptions' => [
                    'deliveryType'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                    'pickupLocation' => [
                        'state' => 'Drenthe',
                    ],
                ],
            ]
        ),
        'output' => [
            'description'                                                                                           => 'myparcel/order/postnl/nl_package',
            'type'                                                                                                  => 'object',
            'additionalItems'                                                                                       => false,
            'required.0'                                                                                            => 'deliveryOptions',
            'required.1'                                                                                            => 'physicalProperties',
            'required.2'                                                                                            => 'recipient',
            'properties.recipient.type'                                                                             => 'object',
            'properties.recipient.required.0'                                                                       => 'cc',
            'properties.recipient.properties.cc.type'                                                               => 'string',
            'properties.recipient.properties.cc.pattern'                                                            => '^[A-z]{2}$',
            'properties.deliveryOptions.type'                                                                       => 'object',
            'properties.deliveryOptions.additionalProperties'                                                       => false,
            'properties.deliveryOptions.properties.carrier.type'                                                    => 'string',
            'properties.deliveryOptions.properties.date.type.0'                                                     => 'string',
            'properties.deliveryOptions.properties.date.type.1'                                                     => 'null',
            'properties.deliveryOptions.properties.date.pattern'                                                    => '([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})',
            'properties.deliveryOptions.properties.deliveryType.type.0'                                             => 'string',
            'properties.deliveryOptions.properties.deliveryType.type.1'                                             => 'null',
            'properties.deliveryOptions.properties.deliveryType.enum.0'                                             => 'standard',
            'properties.deliveryOptions.properties.deliveryType.enum.1'                                             => 'morning',
            'properties.deliveryOptions.properties.deliveryType.enum.2'                                             => 'evening',
            'properties.deliveryOptions.properties.deliveryType.enum.3'                                             => 'pickup',
            'properties.deliveryOptions.properties.deliveryType.enum.4'                                             => null,
            'properties.deliveryOptions.properties.labelAmount.type.0'                                              => 'integer',
            'properties.deliveryOptions.properties.labelAmount.type.1'                                              => 'null',
            'properties.deliveryOptions.properties.packageType.type'                                                => 'string',
            'properties.deliveryOptions.properties.pickupLocation.type.0'                                           => 'object',
            'properties.deliveryOptions.properties.pickupLocation.type.1'                                           => 'null',
            'properties.deliveryOptions.properties.pickupLocation.additionalProperties'                             => false,
            'properties.deliveryOptions.properties.pickupLocation.properties.postalCode.type'                       => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.locationName.type'                     => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.city.type'                             => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.fullStreet.type'                       => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.street.type'                           => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.streetAdditionalInfo.type.0'           => 'null',
            'properties.deliveryOptions.properties.pickupLocation.properties.streetAdditionalInfo.type.1'           => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.number.type'                           => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.numberSuffix.type.0'                   => 'null',
            'properties.deliveryOptions.properties.pickupLocation.properties.numberSuffix.type.1'                   => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.boxNumber.type.0'                      => 'null',
            'properties.deliveryOptions.properties.pickupLocation.properties.boxNumber.type.1'                      => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.boxNumber.maxLength'                   => 8,
            'properties.deliveryOptions.properties.pickupLocation.properties.region.type'                           => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.region.maxLength'                      => 35,
            'properties.deliveryOptions.properties.pickupLocation.properties.state.type'                            => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.cc.type'                               => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.cc.pattern'                            => '^[A-z]{2}$',
            'properties.deliveryOptions.properties.pickupLocation.properties.locationCode.type'                     => 'string',
            'properties.deliveryOptions.properties.pickupLocation.properties.locationCode.minLength'                => 1,
            'properties.deliveryOptions.properties.pickupLocation.properties.retailNetworkId.type'                  => 'string',
            'properties.deliveryOptions.properties.shipmentOptions.type'                                            => 'object',
            'properties.deliveryOptions.properties.shipmentOptions.additionalProperties'                            => false,
            'properties.deliveryOptions.properties.shipmentOptions.properties.ageCheck.type.0'                      => 'boolean',
            'properties.deliveryOptions.properties.shipmentOptions.properties.ageCheck.type.1'                      => 'null',
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.type.0'                     => 'integer',
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.type.1'                     => 'null',
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.0'                     => 0,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.1'                     => 10000,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.2'                     => 25000,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.3'                     => 50000,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.4'                     => 100000,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.5'                     => 150000,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.6'                     => 200000,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.7'                     => 250000,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.8'                     => 300000,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.9'                     => 350000,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.10'                    => 400000,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.11'                    => 450000,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.12'                    => 500000,
            'properties.deliveryOptions.properties.shipmentOptions.properties.insurance.enum.13'                    => null,
            'properties.deliveryOptions.properties.shipmentOptions.properties.labelDescription.type.0'              => 'string',
            'properties.deliveryOptions.properties.shipmentOptions.properties.labelDescription.type.1'              => 'null',
            'properties.deliveryOptions.properties.shipmentOptions.properties.labelDescription.maxLength'           => 45,
            'properties.deliveryOptions.properties.shipmentOptions.properties.largeFormat.type.0'                   => 'boolean',
            'properties.deliveryOptions.properties.shipmentOptions.properties.largeFormat.type.1'                   => 'null',
            'properties.deliveryOptions.properties.shipmentOptions.properties.onlyRecipient.type.0'                 => 'boolean',
            'properties.deliveryOptions.properties.shipmentOptions.properties.onlyRecipient.type.1'                 => 'null',
            'properties.deliveryOptions.properties.shipmentOptions.properties.return.type.0'                        => 'boolean',
            'properties.deliveryOptions.properties.shipmentOptions.properties.return.type.1'                        => 'null',
            'properties.deliveryOptions.properties.shipmentOptions.properties.sameDayDelivery.type.0'               => 'boolean',
            'properties.deliveryOptions.properties.shipmentOptions.properties.sameDayDelivery.type.1'               => 'null',
            'properties.deliveryOptions.properties.shipmentOptions.properties.sameDayDelivery.enum.0'               => false,
            'properties.deliveryOptions.properties.shipmentOptions.properties.signature.type.0'                     => 'boolean',
            'properties.deliveryOptions.properties.shipmentOptions.properties.signature.type.1'                     => 'null',
            'properties.physicalProperties.properties.weight.note'                                                  => 'Do not put weight here, it will take precedence over any (deeper) allOf / anyOf statement',
            'allOf.0.anyOf.0.type'                                                                                  => 'object',
            'allOf.0.anyOf.0.properties.deliveryOptions.type'                                                       => 'object',
            'allOf.0.anyOf.0.properties.deliveryOptions.properties.shipmentOptions.required.0'                      => 'ageCheck',
            'allOf.0.anyOf.0.properties.deliveryOptions.properties.shipmentOptions.properties.ageCheck.enum.0'      => true,
            'allOf.0.anyOf.0.properties.deliveryOptions.properties.shipmentOptions.properties.onlyRecipient.enum.0' => true,
            'allOf.0.anyOf.0.properties.deliveryOptions.properties.shipmentOptions.properties.signature.enum.0'     => true,
            'allOf.0.anyOf.1.type'                                                                                  => 'object',
            'allOf.0.anyOf.1.properties.deliveryOptions.type'                                                       => 'object',
            'allOf.0.anyOf.1.properties.deliveryOptions.properties.shipmentOptions.properties.ageCheck.enum.0'      => null,
            'allOf.0.anyOf.1.properties.deliveryOptions.properties.shipmentOptions.properties.ageCheck.enum.1'      => false,
            'allOf.1.anyOf.0.properties.deliveryOptions.properties.shipmentOptions.required.0'                      => 'largeFormat',
            'allOf.1.anyOf.0.properties.deliveryOptions.properties.shipmentOptions.properties.largeFormat.enum.0'   => true,
            'allOf.1.anyOf.0.properties.physicalProperties.properties.weight.maximum'                               => 30000,
            'allOf.1.anyOf.1.properties.physicalProperties.properties.weight.maximum'                               => 23000,
            'allOf.2.anyOf.0.properties.deliveryOptions.required.0'                                                 => 'deliveryType',
            'allOf.2.anyOf.0.properties.deliveryOptions.properties.deliveryType.enum.0'                             => 'standard',
            'allOf.2.anyOf.0.properties.deliveryOptions.properties.deliveryType.enum.1'                             => null,
            'allOf.2.anyOf.1.properties.deliveryOptions.required.0'                                                 => 'date',
            'allOf.2.anyOf.1.properties.deliveryOptions.properties.date.type'                                       => 'string',
            'allOf.2.anyOf.1.properties.deliveryOptions.properties.date.pattern'                                    => '^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$',
            'allOf.3.anyOf.0.properties.deliveryOptions.required.0'                                                 => 'deliveryType',
            'allOf.3.anyOf.0.properties.deliveryOptions.required.1'                                                 => 'pickupLocation',
            'allOf.3.anyOf.0.properties.deliveryOptions.properties.deliveryType.enum.0'                             => 'pickup',
            'allOf.3.anyOf.0.properties.deliveryOptions.properties.pickupLocation.type'                             => 'object',
            'allOf.3.anyOf.0.properties.deliveryOptions.properties.pickupLocation.properties.locationCode.type'     => 'string',
            'allOf.3.anyOf.0.properties.deliveryOptions.properties.shipmentOptions.properties.onlyRecipient.enum.0' => false,
            'allOf.3.anyOf.0.properties.deliveryOptions.properties.shipmentOptions.properties.signature.enum.0'     => true,
            'allOf.3.anyOf.0.properties.deliveryOptions.properties.shipmentOptions.properties.return.enum.0'        => false,
            'allOf.3.anyOf.1.properties.deliveryOptions.properties.deliveryType.enum.0'                             => 'morning',
            'allOf.3.anyOf.1.properties.deliveryOptions.properties.deliveryType.enum.1'                             => 'standard',
            'allOf.3.anyOf.1.properties.deliveryOptions.properties.deliveryType.enum.2'                             => 'evening',
            'allOf.3.anyOf.1.properties.deliveryOptions.properties.deliveryType.enum.3'                             => null,
        ],
    ],
]);

it('validates order', function (array $input, array $errors = []) {
    $pdkOrder  = new PdkOrder($input);
    $validator = new OrderValidator($pdkOrder);

    $isValid = $validator->validate();

    expect(Arr::dot($validator->getErrors()))
        ->toBe($errors)
        ->and($isValid)
        ->toBe(empty($errors));
})->with([
        'instabox to France'                        => [
            'input'  => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'recipient'       => [
                        'cc' => 'FR',
                    ],
                    'deliveryOptions' => [
                        'carrier' => 'instabox',
                    ],
                ]
            ),
            'errors' => [
                '0.property'   => 'recipient.cc',
                '0.pointer'    => '/recipient/cc',
                '0.message'    => 'Does not have a value in the enumeration ["NL"]',
                '0.constraint' => 'enum',
                '0.context'    => 1,
                '0.enum.0'     => 'NL',
            ],
        ],
        'postnl non-standard delivery without date' => [
            'input'  => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'deliveryOptions' => [
                        'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                        'date'            => null,
                        'shipmentOptions' => ['signature' => true, 'only_recipient' => true],
                    ],
                ]
            ),
            'errors' => [
                '0.property'   => 'deliveryOptions.deliveryType',
                '0.pointer'    => '/deliveryOptions/deliveryType',
                '0.message'    => 'Does not have a value in the enumeration ["standard",null]',
                '0.constraint' => 'enum',
                '0.context'    => 1,
                '0.enum.0'     => 'standard',
                '0.enum.1'     => null,
                '1.property'   => 'deliveryOptions.date',
                '1.pointer'    => '/deliveryOptions/date',
                '1.message'    => 'NULL value found, but a string is required',
                '1.constraint' => 'type',
                '1.context'    => 1,
                '2.property'   => '',
                '2.pointer'    => '',
                '2.message'    => 'Failed to match at least one schema',
                '2.constraint' => 'anyOf',
                '2.context'    => 1,
                '3.property'   => '',
                '3.pointer'    => '',
                '3.message'    => 'Failed to match all schemas',
                '3.constraint' => 'allOf',
                '3.context'    => 1,
            ],
        ],
        'pickup without pickupLocation'             => [
            'input'  => arrayMergeOrder(
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
            'errors' => [
                '0.property'   => 'deliveryOptions.pickupLocation',
                '0.pointer'    => '/deliveryOptions/pickupLocation',
                '0.message'    => 'NULL value found, but an object is required',
                '0.constraint' => 'type',
                '0.context'    => 1,
                '1.property'   => 'deliveryOptions.deliveryType',
                '1.pointer'    => '/deliveryOptions/deliveryType',
                '1.message'    => 'Does not have a value in the enumeration ["morning","standard","evening",null]',
                '1.constraint' => 'enum',
                '1.context'    => 1,
                '1.enum.0'     => 'morning',
                '1.enum.1'     => 'standard',
                '1.enum.2'     => 'evening',
                '1.enum.3'     => null,
                '2.property'   => '',
                '2.pointer'    => '',
                '2.message'    => 'Failed to match at least one schema',
                '2.constraint' => 'anyOf',
                '2.context'    => 1,
                '3.property'   => '',
                '3.pointer'    => '',
                '3.message'    => 'Failed to match all schemas',
                '3.constraint' => 'allOf',
                '3.context'    => 1,
            ],
        ],
        'pickup without location code'              => [
            'input'  => arrayMergeOrder(
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
            'errors' => [
                '0.property'   => 'deliveryOptions.pickupLocation.postalCode',
                '0.pointer'    => '/deliveryOptions/pickupLocation/postalCode',
                '0.message'    => 'NULL value found, but a string is required',
                '0.constraint' => 'type',
                '0.context'    => 1,
                '1.property'   => 'deliveryOptions.pickupLocation.locationName',
                '1.pointer'    => '/deliveryOptions/pickupLocation/locationName',
                '1.message'    => 'NULL value found, but a string is required',
                '1.constraint' => 'type',
                '1.context'    => 1,
                '2.property'   => 'deliveryOptions.pickupLocation.city',
                '2.pointer'    => '/deliveryOptions/pickupLocation/city',
                '2.message'    => 'NULL value found, but a string is required',
                '2.constraint' => 'type',
                '2.context'    => 1,
                '3.property'   => 'deliveryOptions.pickupLocation.fullStreet',
                '3.pointer'    => '/deliveryOptions/pickupLocation/fullStreet',
                '3.message'    => 'NULL value found, but a string is required',
                '3.constraint' => 'type',
                '3.context'    => 1,
                '4.property'   => 'deliveryOptions.pickupLocation.street',
                '4.pointer'    => '/deliveryOptions/pickupLocation/street',
                '4.message'    => 'NULL value found, but a string is required',
                '4.constraint' => 'type',
                '4.context'    => 1,
                '5.property'   => 'deliveryOptions.pickupLocation.number',
                '5.pointer'    => '/deliveryOptions/pickupLocation/number',
                '5.message'    => 'NULL value found, but a string is required',
                '5.constraint' => 'type',
                '5.context'    => 1,
                '6.property'   => 'deliveryOptions.pickupLocation.region',
                '6.pointer'    => '/deliveryOptions/pickupLocation/region',
                '6.message'    => 'NULL value found, but a string is required',
                '6.constraint' => 'type',
                '6.context'    => 1,
                '7.property'   => 'deliveryOptions.pickupLocation.cc',
                '7.pointer'    => '/deliveryOptions/pickupLocation/cc',
                '7.message'    => 'NULL value found, but a string is required',
                '7.constraint' => 'type',
                '7.context'    => 1,
                '8.property'   => 'deliveryOptions.pickupLocation.locationCode',
                '8.pointer'    => '/deliveryOptions/pickupLocation/locationCode',
                '8.message'    => 'NULL value found, but a string is required',
                '8.constraint' => 'type',
                '8.context'    => 1,
                '9.property'   => 'deliveryOptions.pickupLocation.retailNetworkId',
                '9.pointer'    => '/deliveryOptions/pickupLocation/retailNetworkId',
                '9.message'    => 'NULL value found, but a string is required',
                '9.constraint' => 'type',
                '9.context'    => 1,
            ],
        ],
        'package without country'                   => [
            'input'  => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'recipient' => [
                        'cc' => null,
                    ],
                ]
            ),
            'errors' => [
                '0.property'   => 'recipient.cc',
                '0.pointer'    => '/recipient/cc',
                '0.message'    => 'NULL value found, but a string is required',
                '0.constraint' => 'type',
                '0.context'    => 1,
            ],
        ],
        'postnl with same day delivery'             => [
            'input'  => arrayMergeOrder(
                STANDARD_INPUT,
                ['deliveryOptions' => ['shipmentOptions' => ['sameDayDelivery' => true]]]
            ),
            'errors' => [
                '0.property'   => 'deliveryOptions.shipmentOptions.sameDayDelivery',
                '0.pointer'    => '/deliveryOptions/shipmentOptions/sameDayDelivery',
                '0.message'    => 'Does not have a value in the enumeration [false]',
                '0.constraint' => 'enum',
                '0.context'    => 1,
                '0.enum.0'     => false,
            ],
        ],
        'morning delivery with age check'           => [
            'input'  => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'deliveryOptions' => [
                        'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                        'shipmentOptions' => ['ageCheck' => true],
                    ],
                ]
            ),
            'errors' => [
                '0.property'   => 'deliveryOptions.shipmentOptions.onlyRecipient',
                '0.pointer'    => '/deliveryOptions/shipmentOptions/onlyRecipient',
                '0.message'    => 'Does not have a value in the enumeration [true]',
                '0.constraint' => 'enum',
                '0.context'    => 1,
                '0.enum.0'     => true,
                '1.property'   => 'deliveryOptions.shipmentOptions.signature',
                '1.pointer'    => '/deliveryOptions/shipmentOptions/signature',
                '1.message'    => 'Does not have a value in the enumeration [true]',
                '1.constraint' => 'enum',
                '1.context'    => 1,
                '1.enum.0'     => true,
                '2.property'   => 'deliveryOptions.shipmentOptions.ageCheck',
                '2.pointer'    => '/deliveryOptions/shipmentOptions/ageCheck',
                '2.message'    => 'Does not have a value in the enumeration [null,false]',
                '2.constraint' => 'enum',
                '2.context'    => 1,
                '2.enum.0'     => null,
                '2.enum.1'     => false,
                '3.property'   => '',
                '3.pointer'    => '',
                '3.message'    => 'Failed to match at least one schema',
                '3.constraint' => 'anyOf',
                '3.context'    => 1,
                '4.property'   => '',
                '4.pointer'    => '',
                '4.message'    => 'Failed to match all schemas',
                '4.constraint' => 'allOf',
                '4.context'    => 1,
                '5.property'   => 'deliveryOptions.shipmentOptions.ageCheck',
                '5.pointer'    => '/deliveryOptions/shipmentOptions/ageCheck',
                '5.message'    => 'Does not have a value in the enumeration [false]',
                '5.constraint' => 'enum',
                '5.context'    => 1,
                '5.enum.0'     => false,
            ],
        ],
        'weight of 25kg'                            => [
            'input'  => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'physicalProperties' => ['weight' => 25000],
                ]
            ),
            'errors' => [
                '0.property'   => 'deliveryOptions.shipmentOptions.largeFormat',
                '0.pointer'    => '/deliveryOptions/shipmentOptions/largeFormat',
                '0.message'    => 'Does not have a value in the enumeration [true]',
                '0.constraint' => 'enum',
                '0.context'    => 1,
                '0.enum.0'     => true,
                '1.property'   => 'physicalProperties.weight',
                '1.pointer'    => '/physicalProperties/weight',
                '1.message'    => 'Must have a maximum value of 23000',
                '1.constraint' => 'maximum',
                '1.context'    => 1,
                '1.maximum'    => 23000,
                '2.property'   => '',
                '2.pointer'    => '',
                '2.message'    => 'Failed to match at least one schema',
                '2.constraint' => 'anyOf',
                '2.context'    => 1,
                '3.property'   => '',
                '3.pointer'    => '',
                '3.message'    => 'Failed to match all schemas',
                '3.constraint' => 'allOf',
                '3.context'    => 1,
            ],
        ],
        'weight of 29kg with large_format'          => [
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
        'mailbox with morning delivery'             => [
            'input'  => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'deliveryOptions' => [
                        'deliveryType' => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                        'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                    ],
                ]
            ),
            'errors' => [
                '0.property'   => 'deliveryOptions.deliveryType',
                '0.pointer'    => '/deliveryOptions/deliveryType',
                '0.message'    => 'Does not have a value in the enumeration ["standard","pickup",null]',
                '0.constraint' => 'enum',
                '0.context'    => 1,
                '0.enum.0'     => 'standard',
                '0.enum.1'     => 'pickup',
                '0.enum.2'     => null,
            ],
        ],
        'EU package without insurance'              => [
            'input'  => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'recipient'       => [
                        'cc' => 'FR',
                    ],
                    'deliveryOptions' => ['shipmentOptions' => ['insurance' => null]],
                ]
            ),
            'errors' => [
                '0.property'   => 'deliveryOptions.shipmentOptions.insurance',
                '0.pointer'    => '/deliveryOptions/shipmentOptions/insurance',
                '0.message'    => 'Does not have a value in the enumeration [50000]',
                '0.constraint' => 'enum',
                '0.context'    => 1,
                '0.enum.0'     => 50000,
            ],
        ],
        'EU package with no weight'                 => [
            'input'  => arrayMergeOrder(
                STANDARD_INPUT,
                [
                    'recipient'          => [
                        'cc' => 'FR',
                    ],
                    'deliveryOptions'    => ['shipmentOptions' => ['insurance' => 50000]],
                    'physicalProperties' => ['weight' => 0],
                ]
            ),
            'errors' => [
                '0.property'   => 'physicalProperties.weight',
                '0.pointer'    => '/physicalProperties/weight',
                '0.message'    => 'Must have a minimum value of 1',
                '0.constraint' => 'minimum',
                '0.context'    => 1,
                '0.minimum'    => 1,
            ],
        ],
        'EU package with correct insurance'         => [
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
        'mailbox with shipmentOptions'              => [
            'input'  => arrayMergeOrder(
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
            'errors' => [
                '0.property'   => 'deliveryOptions.shipmentOptions.signature',
                '0.pointer'    => '/deliveryOptions/shipmentOptions/signature',
                '0.message'    => 'Does not have a value in the enumeration [false]',
                '0.constraint' => 'enum',
                '0.context'    => 1,
                '0.enum.0'     => false,
            ],
        ],
        'BE mailbox'                                => [
            'input'  => arrayMergeOrder(
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
            'errors' => [
                '0.property'   => 'deliveryOptions.packageType',
                '0.pointer'    => '/deliveryOptions/packageType',
                '0.message'    => 'Does not have a value in the enumeration ["letter","package"]',
                '0.constraint' => 'enum',
                '0.context'    => 1,
                '0.enum.0'     => 'letter',
                '0.enum.1'     => 'package',
            ],
        ],
        'ROW package'                               => [
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
    ]
);
