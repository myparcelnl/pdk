<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());
it('creates fulfilment order from pdk order', function (array $input) {
    $pdkOrder = new PdkOrder($input);

    $fulfilmentOrder = Order::fromPdkOrder($pdkOrder);
    expect($fulfilmentOrder)->toBeInstanceOf(Order::class);
    assertMatchesJsonSnapshot(json_encode($fulfilmentOrder->toArray()));
})->with([
    'empty order'             => [[]],
    'order without shipments' => [
        'input' => [
            'externalIdentifier'    => 'ABC123456',
            'orderDate'             => '2023-01-01 00:00:00',
            'customsDeclaration'    => [
                'contents' => '00',
                'invoice'  => 'ABC123456',
                'items'    => [
                    [
                        'amount'         => 10,
                        'classification' => '12345',
                        'country'        => 'NL',
                        'description'    => 'A word',
                        'itemValue'      => [
                            'amount'   => 100,
                            'currency' => 'EUR',
                        ],
                        'weight'         => 100,
                    ],
                ],
                'weight'   => 1000,
            ],
            'deliveryOptions'       => [
                'carrier'         => 'postnl',
                'date'            => '2020-01-01',
                'deliveryType'    => 'standard',
                'labelAmount'     => 1,
                'packageType'     => 'package',
                'pickupLocation'  => null,
                'shipmentOptions' => [
                    'onlyRecipient' => true,
                    'signature'     => true,
                ],
            ],
            'lines'                 => [
                [
                    'externalIdentifier' => 'ABC123456-1',
                    'quantity'           => 10,
                    'price'              => 295,
                    'vat'                => 62,
                    'priceAfterVat'      => 357,
                    'product'            => [
                        'sku'    => 'ABC123456',
                        'ean'    => '1234567890123',
                        'name'   => 'Product name',
                        'weight' => 100,
                    ],
                ],
            ],
            'physicalProperties'    => [
                'weight' => 1000,
                'height' => 100,
                'width'  => 100,
                'length' => 100,
            ],
            'recipient'             => [
                'company'    => 'MyParcel',
                'email'      => 'test@myparcel.nl',
                'person'     => 'Ms. Parcel',
                'phone'      => '0612356789',
                'cc'         => 'NL',
                'city'       => 'Hoofddorp',
                'address1'   => 'Antareslaan 31',
                'postalCode' => '2132JE',
                'region'     => 'Noord-Holland',
                'state'      => 'Noord-Holland',
            ],
            'sender'                => null,
            'shipments'             => [],
            'shipmentPrice'         => 695,
            'shipmentVat'           => 146,
            'shipmentPriceAfterVat' => 841,
            'orderPrice'            => 2950,
            'orderVat'              => 620,
            'orderPriceAfterVat'    => 3570,
            'totalPrice'            => 3645,
            'totalVat'              => 766,
            'totalPriceAfterVat'    => 4411,
        ],
    ],
    'order with shipments'    => [
        'input' => [
            'shipments' => [
                [
                    'deliveryOptions' => [
                        'carrier'         => 'postnl',
                        'date'            => '2020-01-01',
                        'deliveryType'    => 'standard',
                        'labelAmount'     => 1,
                        'packageType'     => 'package',
                        'pickupLocation'  => null,
                        'shipmentOptions' => [
                            'onlyRecipient' => true,
                            'signature'     => true,
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

it('returns empty fulfilment order when no pdk order is passed', function () {
    $fulfilmentOrder = Order::fromPdkOrder(null);
    expect($fulfilmentOrder)->toBeInstanceOf(Order::class);
    assertMatchesJsonSnapshot(json_encode($fulfilmentOrder->toArray()));
});

