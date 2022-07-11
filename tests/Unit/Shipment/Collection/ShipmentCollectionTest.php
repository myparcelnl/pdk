<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Options\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Sdk\src\Exception\ValidationException;

it('throws error on export when collection is empty', function () {
    $collection = new ShipmentCollection();

    expect(function () use ($collection) {
        $collection->export();
    })->toThrow(RuntimeException::class);
});

it('throws error on export when shipment(s) are invalid', function () {
    $postNLShipment                  = new Shipment(['carrier' => 'postnl']);
    $postNLShipment->deliveryOptions = new DeliveryOptions([
        'deliveryType' => 5,
    ]);

    $collection = new ShipmentCollection([
        $postNLShipment,
    ]);

    expect(function () use ($collection) {
        $collection->export();
    })->toThrow(ValidationException::class);
});
