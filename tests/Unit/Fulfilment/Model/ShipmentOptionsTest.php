<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageType;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ShipmentResponsesDeliveryOptionsPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ShipmentDefsDeliveryOptionsDeliveryNameV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryType;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('can create instance from pdk delivery options', function () {
    $deliveryOptions = factory(DeliveryOptions::class)
        ->withCarrier('POSTNL')
        ->withDeliveryType(ShipmentDefsDeliveryOptionsDeliveryNameV2::MORNING)
        ->withPackageType(ShipmentResponsesDeliveryOptionsPackageTypeV2::MAILBOX)
        ->withAllShipmentOptions()
        ->make();

    $created = ShipmentOptions::fromPdkDeliveryOptions($deliveryOptions);

    expect($created->toArray())->toEqual([
        'deliveryType'     => RefTypesDeliveryType::MORNING,
        'packageType'      => RefShipmentPackageType::MAILBOX,
        'deliveryDate'     => null,
        'insurance'        => 100,
        'labelDescription' => 'test',
        'ageCheck'         => true,
        'collect'          => false,
        'hideSender'       => true,
        'largeFormat'      => true,
        'onlyRecipient'    => true,
        'priorityDelivery' => true,
        'return'           => true,
        'sameDayDelivery'  => true,
        'saturdayDelivery' => false,
        'signature'        => true,
        'receiptCode'      => true,
        'noTracking'          => false,
        'freshFood'        => false,
        'frozen'           => false,
    ]);
});
