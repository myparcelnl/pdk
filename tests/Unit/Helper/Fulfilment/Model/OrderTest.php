<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

it('creates fulfilment order from pdk order', function () {
    PdkFactory::create(MockPdkConfig::create());

    $pdkOrder = new PdkOrder([
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
        'label'                 => null,
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
            'fullStreet' => 'Antareslaan 31',
            'postalCode' => '2131JE',
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
    ]);

    $fulfilmentOrder = Order::fromPdkOrder($pdkOrder);

    expect($fulfilmentOrder)
        ->toBeInstanceOf(Order::class)
        ->and($fulfilmentOrder->toArray())
        ->toBe([
            'uuid'                        => null,
            'externalIdentifier'          => 'ABC123456',
            'fulfilmentPartnerIdentifier' => null,
            'shopId'                      => null,
            'accountId'                   => null,
            'invoiceAddress'              => null,
            'language'                    => 'nl',
            'orderDate'                   => '2023-01-01 00:00:00',
            'orderLines'                  => [
                [
                    'uuid'          => null,
                    'quantity'      => 10,
                    'price'         => 295,
                    'vat'           => 62,
                    'priceAfterVat' => 357,
                    'product'       => [
                        'uuid'               => null,
                        'sku'                => 'ABC123456',
                        'ean'                => '1234567890123',
                        'externalIdentifier' => null,
                        'name'               => 'Product name',
                        'description'        => null,
                        'width'              => 0,
                        'length'             => 0,
                        'height'             => 0,
                        'weight'             => 100,
                    ],
                ],
            ],
            'shipment'                    => [
                'carrier'            => null,
                'contractId'         => null,
                'customsDeclaration' => [
                    'contents' => 1,
                    'invoice'  => null,
                    'items'    => [],
                    'weight'   => 0,
                ],
                'options'            => null,
                'pickup'             => null,
                'recipient'          => [
                    'boxNumber'            => null,
                    'cc'                   => 'NL',
                    'city'                 => 'Hoofddorp',
                    'fullStreet'           => 'Antareslaan 31',
                    'number'               => '31',
                    'numberSuffix'         => null,
                    'postalCode'           => '2131JE',
                    'region'               => 'Noord-Holland',
                    'state'                => 'Noord-Holland',
                    'street'               => 'Antareslaan',
                    'streetAdditionalInfo' => null,
                    'email'                => 'test@myparcel.nl',
                    'phone'                => '0612356789',
                    'person'               => 'Ms. Parcel',
                    'company'              => 'MyParcel',
                ],
            ],
            'status'                      => null,
            'type'                        => null,
            'price'                       => 2950,
            'vat'                         => 620,
            'priceAfterVat'               => 3570,
            'createdAt'                   => null,
            'updatedAt'                   => null,
        ]);
});
