<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesNotificationsMock;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Pdk;

usesShared(new UsesMockPdkInstance(), new UsesNotificationsMock());

it('holds PdkOrders', function () {
    $pdkOrderCollection = new PdkOrderCollection([
        ['externalIdentifier' => 'MP-1'],
        ['externalIdentifier' => 'MP-2'],
    ]);

    expect($pdkOrderCollection->count())
        ->toBe(2)
        ->and(
            $pdkOrderCollection->every(function ($pdkOrder) {
                return $pdkOrder instanceof PdkOrder;
            })
        )
        ->toBeTrue()
        ->and($pdkOrderCollection->every('externalIdentifier', '!=', null))
        ->toBeTrue();
});

it('can generate a shipment on each order', function () {
    $pdkOrderCollection = new PdkOrderCollection([
        new PdkOrder(['externalIdentifier' => 'MP-1', 'recipient' => ['cc' => 'NL']]),
        new PdkOrder(['externalIdentifier' => 'MP-2', 'recipient' => ['cc' => 'NL']]),
    ]);

    $pdkOrderCollection->generateShipments();
    $pdkOrderCollection->generateShipments();

    expect($pdkOrderCollection->count())
        ->toBe(2)
        ->and(
            $pdkOrderCollection->every(function (PdkOrder $order) {
                return 2 === $order->shipments->count();
            })
        )
        ->toBeTrue();
});

it('gets all shipments of all orders', function () {
    $pdkOrderCollection = new PdkOrderCollection([
        [
            'externalIdentifier' => 'MP-1',
            'shipments'          => [['id' => 100020]],
        ],
        [
            'externalIdentifier' => 'MP-2',
            'shipments'          => [['id' => 100021]],
        ],
    ]);

    $shipments = $pdkOrderCollection->getAllShipments();
    $array     = $shipments->toArray();

    expect($shipments)
        ->toBeInstanceOf(ShipmentCollection::class)
        ->and(Arr::pluck($array, 'id'))
        ->toEqual([100020, 100021])
        ->and(array_keys($array))
        ->toEqual([0, 1]);
});

it('gets shipments by shipment ids', function () {
    $pdkOrderCollection = new PdkOrderCollection([
        [
            'externalIdentifier' => '🐝',
            'shipments'          => [['id' => 29090], ['id' => 30000]],
        ],
        [
            'externalIdentifier' => '🦉',
            'shipments'          => [['id' => 30010]],
        ],
    ]);

    $shipments = $pdkOrderCollection->getShipmentsByIds([29090, 30010]);
    $array     = $shipments->toArray();

    expect($shipments)
        ->toBeInstanceOf(ShipmentCollection::class)
        ->and(Arr::pluck($array, 'id'))
        ->toEqual([29090, 30010])
        ->and(array_keys($array))
        ->toEqual([0, 1]);
});

it('updates order shipments by shipment ids', function () {
    $orders = new PdkOrderCollection([
        [
            'externalIdentifier' => '🐰',
            'shipments'          => [
                ['id' => 29090, 'status' => 1],
                ['id' => 30000, 'status' => 1],
            ],
        ],
        [
            'externalIdentifier' => '🐸',
            'shipments'          => [
                ['id' => 30010, 'status' => 1],
            ],
        ],
        [
            'externalIdentifier' => '🐷',
            'shipments'          => [],
        ],
        [
            'externalIdentifier' => '🦊',
            'shipments'          => [
                ['id' => 30070, 'status' => 1],
            ],
        ],
    ]);

    $orders->updateShipments(
        new ShipmentCollection([
            ['id' => 30000, 'status' => 7, 'orderId' => '🐰'],
            ['id' => 30010, 'status' => 7, 'orderId' => '🐸'],
            ['id' => 30020, 'status' => 7],
        ])
    );

    // TODO: simplify when collections support "only" method
    $shipments = array_map(function (array $shipment) {
        return Arr::only($shipment, ['id', 'orderId', 'status']);
    },
        $orders->getAllShipments()
            ->toArray());

    expect($shipments)->toBe([
        ['orderId' => '🐰', 'id' => 29090, 'status' => 1],
        ['orderId' => '🐰', 'id' => 30000, 'status' => 7],
        ['orderId' => '🐸', 'id' => 30010, 'status' => 7],
        ['orderId' => '🦊', 'id' => 30070, 'status' => 1],
    ]);
});

it('updates order shipments by order ids', function () {
    $orders = new PdkOrderCollection([
        [
            'externalIdentifier' => '🐰',
            'shipments'          => [
                ['id' => 29090, 'status' => 1],
                ['id' => 30000, 'status' => 1],
            ],
        ],
        [
            'externalIdentifier' => '🐸',
            'shipments'          => [
                ['id' => 30010, 'status' => 1],
            ],
        ],
        [
            'externalIdentifier' => '🐷',
            'shipments'          => [],
        ],
        [
            'externalIdentifier' => '🦊',
            'shipments'          => [
                ['id' => 30070, 'status' => 1],
            ],
        ],
    ]);

    $orders->updateShipments(
        new ShipmentCollection([
            ['orderId' => '🐰', 'id' => 30000, 'status' => 7],
            ['orderId' => '🐸', 'id' => 40000, 'status' => 7],
            ['orderId' => '🐷', 'id' => 30020, 'status' => 7],
            ['orderId' => '🐸', 'id' => 30010, 'status' => 3],
        ])
    );

    // TODO: simplify when collections support "only" method
    $shipments = array_map(function (array $shipment) {
        return Arr::only($shipment, ['id', 'orderId', 'status']);
    },
        $orders->getAllShipments()
            ->toArray());

    expect($shipments)->toBe([
        ['orderId' => '🐰', 'id' => 29090, 'status' => 1],
        ['orderId' => '🐰', 'id' => 30000, 'status' => 7],
        ['orderId' => '🐸', 'id' => 30010, 'status' => 3],
        ['orderId' => '🐸', 'id' => 40000, 'status' => 7],
        ['orderId' => '🐷', 'id' => 30020, 'status' => 7],
        ['orderId' => '🦊', 'id' => 30070, 'status' => 1],
    ]);
});

it('can generate return shipments', function () {
    // Create a mock for CarrierSchema
    $carrierSchema = new class extends CarrierSchema {
        public function setCarrier(Carrier $carrier): CarrierSchema
        {
            return $this;
        }

        public function hasReturnCapabilities()
        {
            return true;
        }
    };

    mockPdkProperties([CarrierSchema::class => $carrierSchema]);

    $pdkOrderCollection = new PdkOrderCollection([
        new PdkOrder([
            'externalIdentifier' => 'MP-1',
            'shipments'          => [
                [
                    'id'                  => 100020,
                    'carrier'             => ['name' => 'postnl'],
                    'referenceIdentifier' => 'REF-1',
                    'isReturn'            => false,
                ],
            ],
        ]),
        new PdkOrder([
            'externalIdentifier' => 'MP-2',
            'shipments'          => [
                [
                    'id'                  => 100021,
                    'carrier'             => ['name' => 'postnl'],
                    'referenceIdentifier' => 'REF-2',
                    'isReturn'            => false,
                ],
            ],
        ]),
    ]);

    $returnShipments = $pdkOrderCollection->generateReturnShipments();

    expect($returnShipments)
        ->toBeInstanceOf(ShipmentCollection::class)
        ->and($returnShipments->count())
        ->toBe(2)
        ->and($returnShipments->every('isReturn', '===', true))
        ->toBeTrue();
});

it('skips shipments from carriers without return capabilities when generating return shipments', function () {
    // Create a mock for CarrierSchema
    $carrierSchema = new class extends CarrierSchema {
        public function setCarrier(Carrier $carrier): CarrierSchema
        {
            return $this;
        }

        public function hasReturnCapabilities()
        {
            return false;
        }
    };

    mockPdkProperties([CarrierSchema::class => $carrierSchema]);

    $pdkOrderCollection = new PdkOrderCollection([
        new PdkOrder([
            'externalIdentifier' => 'MP-1',
            'shipments'          => [
                [
                    'id'                  => 100020,
                    'carrier'             => ['name' => 'dhl', 'human' => 'DHL'],
                    'referenceIdentifier' => 'REF-1',
                    'isReturn'            => false,
                ],
            ],
        ]),
    ]);

    $returnShipments = $pdkOrderCollection->generateReturnShipments();

    // Check if a warning notification was added
    $notifications = Notifications::all();

    expect($returnShipments)
        ->toBeInstanceOf(ShipmentCollection::class)
        ->and($returnShipments->count())
        ->toBe(1)
        ->and($returnShipments->first()->isReturn)
        ->toBeFalsy()
        ->and($notifications->count())
        ->toBeGreaterThan(0);
});

it('updates recipient data for shipments when missing', function () {
    // Create an order with shipping address but shipment without recipient
    $orders = new PdkOrderCollection([
        new PdkOrder([
            'externalIdentifier' => 'ORDER-1',
            'shippingAddress'    => [
                'address1'   => 'Test Street 123',
                'address2'   => 'Apartment 4B',
                'cc'         => 'NL',
                'city'       => 'Amsterdam',
                'postalCode' => '1234AB',
                'region'     => 'North Holland',
                'state'      => 'NH',
                'email'      => 'test@example.com',
                'phone'      => '0612345678',
                'person'     => 'John Doe',
                'company'    => 'Test Company',
            ],
            'shipments'          => [
                [
                    'id'     => 12345,
                    'status' => 1,
                    // No recipient data
                ],
            ],
        ]),
    ]);

    // Create a shipment collection with a shipment that matches the order's shipment
    $shipments = new ShipmentCollection([
        [
            'id'      => 12345,
            'status'  => 2,
            'orderId' => 'ORDER-1',
            // No recipient data
        ],
    ]);

    // Update the shipments
    $orders->updateShipments($shipments);

    // Get all shipments from the orders
    $updatedShipments = $orders->getAllShipments();

    // Check if the recipient data was added to the shipment
    expect($updatedShipments->count())
        ->toBe(1)
        ->and($updatedShipments->first()->recipient)
        ->not->toBeNull()
        ->and($updatedShipments->first()->recipient->address1)
        ->toBe('Test Street 123')
        ->and($updatedShipments->first()->recipient->email)
        ->toBe('test@example.com')
        ->and($updatedShipments->first()->recipient->person)
        ->toBe('John Doe')
        ->and($updatedShipments->first()->status)
        ->toBe(2); // Check if the status was updated
});

it('updates shipment recipient data when missing', function () {
    $orders = new PdkOrderCollection([
        [
            'externalIdentifier' => 'ORDER-1',
            'shippingAddress'    => [
                'address1'   => 'Main Street',
                'address2'   => 'Apt 123',
                'cc'         => 'NL',
                'city'       => 'Amsterdam',
                'postalCode' => '1234AB',
                'region'     => 'Noord-Holland',
                'state'      => 'NH',
                'email'      => 'test@example.com',
                'phone'      => '0612345678',
                'person'     => 'John Doe',
                'company'    => 'Test Company',
            ],
            'shipments'          => [
                ['id' => 100001, 'recipient' => null],
            ],
        ],
    ]);

    // Create a shipment without recipient data
    $shipmentToUpdate = new ShipmentCollection([
        [
            'id'        => 100001,
            'orderId'   => 'ORDER-1',
            'recipient' => null,
        ],
    ]);

    // Update the shipments
    $orders->updateShipments($shipmentToUpdate);

    // Get the updated shipment
    $updatedShipment = $orders->getShipmentsByIds([100001])
        ->first();

    // Check if recipient data was updated
    expect($updatedShipment->recipient)->not->toBeNull()
        ->and($updatedShipment->recipient->address1)
        ->toBe('Main Street')
        ->and($updatedShipment->recipient->address2)
        ->toBe('Apt 123')
        ->and($updatedShipment->recipient->cc)
        ->toBe('NL')
        ->and($updatedShipment->recipient->city)
        ->toBe('Amsterdam')
        ->and($updatedShipment->recipient->postalCode)
        ->toBe('1234AB')
        ->and($updatedShipment->recipient->region)
        ->toBe('Noord-Holland')
        ->and($updatedShipment->recipient->state)
        ->toBe('NH')
        ->and($updatedShipment->recipient->email)
        ->toBe('test@example.com')
        ->and($updatedShipment->recipient->phone)
        ->toBe('0612345678')
        ->and($updatedShipment->recipient->person)
        ->toBe('John Doe')
        ->and($updatedShipment->recipient->company)
        ->toBe('Test Company');
});

it('creates contact details from shipping address', function () {
    $orderCollection = new PdkOrderCollection();

    // Create a mock order with shipping address
    $order = new PdkOrder([
        'externalIdentifier' => 'ORDER-1',
        'shippingAddress'    => [
            'address1'   => 'Main Street',
            'address2'   => 'Apt 123',
            'cc'         => 'NL',
            'city'       => 'Amsterdam',
            'postalCode' => '1234AB',
            'region'     => 'Noord-Holland',
            'state'      => 'NH',
            'email'      => 'test@example.com',
            'phone'      => '0612345678',
            'person'     => 'John Doe',
            'company'    => 'Test Company',
        ],
    ]);

    // Create a shipment without recipient
    $shipmentCollection = new ShipmentCollection([
        [
            'id'        => 100001,
            'orderId'   => 'ORDER-1',
            'recipient' => null,
        ],
    ]);
    $shipment           = $shipmentCollection->first();

    // Add the order to the collection
    $orderCollection->push($order);

    // Test the private method indirectly by updating shipments
    $shipments = new ShipmentCollection([$shipment]);
    $orderCollection->updateShipments($shipments);

    // Get the updated shipment
    $updatedShipment = $orderCollection->getShipmentsByIds([100001])
        ->first();

    // Verify that the contact details were created correctly
    expect($updatedShipment->recipient)->not->toBeNull()
        ->and($updatedShipment->recipient->address1)
        ->toBe('Main Street')
        ->and($updatedShipment->recipient->address2)
        ->toBe('Apt 123')
        ->and($updatedShipment->recipient->cc)
        ->toBe('NL')
        ->and($updatedShipment->recipient->city)
        ->toBe('Amsterdam')
        ->and($updatedShipment->recipient->postalCode)
        ->toBe('1234AB')
        ->and($updatedShipment->recipient->region)
        ->toBe('Noord-Holland')
        ->and($updatedShipment->recipient->state)
        ->toBe('NH')
        ->and($updatedShipment->recipient->email)
        ->toBe('test@example.com')
        ->and($updatedShipment->recipient->phone)
        ->toBe('0612345678')
        ->and($updatedShipment->recipient->person)
        ->toBe('John Doe')
        ->and($updatedShipment->recipient->company)
        ->toBe('Test Company');
});
