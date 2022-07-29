<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

it('instantiates shipments', function (array $input) {
    $order = new PdkOrder($input);

    expect($order->shipments)
        ->toBeInstanceOf(ShipmentCollection::class)
        ->and(
            $order->shipments->every(function ($item) {
                return is_a($item, Shipment::class);
            })
        );
})->with([
    'empty shipments' => [
        'input' => [
            'shipments' => [
                [],
            ],
        ],
    ],

    'no shipments' => [
        'input' => [
            'shipments' => [
            ],
        ],
    ],
]);
