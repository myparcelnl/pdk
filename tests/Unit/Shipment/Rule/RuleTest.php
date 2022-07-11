<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Shipment\Model\Options\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Rule\DeliveryTypeRule;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierInstabox;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;

$dataset = [
    'deliveryType'            => [
        'ruleClass'         => DeliveryTypeRule::class,
        'validationSubject' => new Shipment([
            'carrier'         => CarrierPostNL::ID,
            'deliveryOptions' => new DeliveryOptions(['deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME]),
        ]),
        'errors'            => [],
    ],
    'disallowed deliveryType' => [
        'ruleClass'         => DeliveryTypeRule::class,
        'validationSubject' => new Shipment([
            'carrier'         => CarrierInstabox::ID,
            'deliveryOptions' => new DeliveryOptions(['deliveryType' => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME]),
        ]),
        'errors'            => [
            'Delivery type evening is not allowed for Instabox',
        ],
    ],
];

it('validates rules', function ($ruleClass, $validationSubject, $errors) {
    /** @var \MyParcelNL\Sdk\src\Rule\Rule $rule */
    $rule = new $ruleClass();
    $rule->validate($validationSubject);
    expect(
        $rule->getErrors()
            ->toArray()
    )->toBe($errors);
})->with($dataset);
