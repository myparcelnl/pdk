<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetOrdersResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

/**
 * @covers \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository::query
 */

it('creates order collection from queried data', function (string $responseClass, array $output) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new $responseClass());

    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository $repository */
    $repository = $pdk->get(OrderRepository::class);

    $response = $repository->query([]);
    $order    = $response->first();
    $array    = $order->toArray();

    // No need to test this data here.
    $arrayWithoutCapabilities = Arr::except(
        $array,
        ['shipment.carrier.capabilities', 'shipment.carrier.returnCapabilities']
    );

    expect(Arr::dot($arrayWithoutCapabilities))
        ->toBe($output);
})->with([
    'normal shipment' => [
        'response' => ExampleGetOrdersResponse::class,
        'output'   => [
            'accountId'                               => null,
            'createdAt'                               => null,
            'externalIdentifier'                      => null,
            'fulfilmentPartnerIdentifier'             => null,
            'invoiceAddress.boxNumber'                => null,
            'invoiceAddress.cc'                       => 'NL',
            'invoiceAddress.city'                     => 'Boskoop',
            'invoiceAddress.fullStreet'               => null,
            'invoiceAddress.number'                   => null,
            'invoiceAddress.numberSuffix'             => null,
            'invoiceAddress.postalCode'               => null,
            'invoiceAddress.region'                   => null,
            'invoiceAddress.state'                    => null,
            'invoiceAddress.street'                   => null,
            'invoiceAddress.streetAdditionalInfo'     => null,
            'invoiceAddress.email'                    => null,
            'invoiceAddress.phone'                    => null,
            'invoiceAddress.person'                   => null,
            'invoiceAddress.company'                  => null,
            'language'                                => null,
            'orderDate'                               => '2022-08-22 00:00:00',
            'orderLines.0.uuid'                       => '1234',
            'orderLines.0.quantity'                   => 1,
            'orderLines.0.price'                      => 250,
            'orderLines.0.vat'                        => 10,
            'orderLines.0.priceAfterVat'              => 260,
            'orderLines.0.product.uuid'               => '12345',
            'orderLines.0.product.sku'                => '018234',
            'orderLines.0.product.ean'                => '018234',
            'orderLines.0.product.externalIdentifier' => '018234',
            'orderLines.0.product.name'               => 'Paarse stofzuiger',
            'orderLines.0.product.description'        => 'Een paars object waarmee stof opgezogen kan worden',
            'orderLines.0.product.width'              => null,
            'orderLines.0.product.length'             => null,
            'orderLines.0.product.height'             => null,
            'orderLines.0.product.weight'             => 3500,
            'price'                                   => 260,
            'priceAfterVat'                           => null,
            'shipments'                               => [],
            'shopId'                                  => null,
            'status'                                  => null,
            'type'                                    => null,
            'updatedAt'                               => null,
            'uuid'                                    => null,
            'vat'                                     => null,
        ],
    ],
]);
