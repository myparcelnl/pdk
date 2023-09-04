<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('can create instance from pdk delivery options', function () {
    $deliveryOptions = factory(DeliveryOptions::class)
        ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_MORNING_NAME)
        ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
        ->withAllShipmentOptions()
        ->make();

    $created = ShipmentOptions::fromPdkDeliveryOptions($deliveryOptions);

    expect($created->toArray())->toEqual([
        'deliveryType'     => DeliveryOptions::DELIVERY_TYPE_MORNING_ID,
        'packageType'      => DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID,
        'deliveryDate'     => null,
        'insurance'        => 100,
        'labelDescription' => 'test',
        'ageCheck'         => true,
        'collect'          => null,
        'cooledDelivery'   => null,
        'hideSender'       => true,
        'largeFormat'      => true,
        'onlyRecipient'    => true,
        'return'           => true,
        'sameDayDelivery'  => true,
        'saturdayDelivery' => null,
        'signature'        => true,
    ]);
});
