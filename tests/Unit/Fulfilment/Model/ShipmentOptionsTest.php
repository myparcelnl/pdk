<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('can create instance from pdk delivery options', function () {
    $deliveryOptions = factory(DeliveryOptions::class)
        ->withCarrier('POSTNL')
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
        'collect'          => false,
        'cooledDelivery'   => false,
        'hideSender'       => true,
        'largeFormat'      => true,
        'onlyRecipient'    => true,
        'priorityDelivery' => true,
        'return'           => true,
        'sameDayDelivery'  => true,
        'saturdayDelivery' => false,
        'signature'        => true,
        'receiptCode'      => true,
        'tracked'          => false,
        'excludeParcelLockers' => false,
        'freshFood'        => false,
        'frozen'           => false,
    ]);
});
