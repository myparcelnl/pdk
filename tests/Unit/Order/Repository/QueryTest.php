<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Tests\Api\Response\GetOrdersResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

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

    expect($array)
        ->toEqual($output);
})->with([
    'normal shipment' => [
        'response' => GetOrdersResponse::class,
        'output'   => [
            'externalIdentifier'                                        => null,
            'status'                                                    => null,
            'shopId'                                                    => null,
            'accountId'                                                 => null,
            'createdAt'                                                 => null,
            'expectedDeliveryDate'                                      => null,
            'expectedDeliveryTimeframe'                                 => null,
            'fulfilmentPartnerIdentifier'                               => null,
            'invoiceAddress.boxNumber'                                  => null,
            'invoiceAddress.cc'                                         => 'NL',
            'invoiceAddress.city'                                       => 'Boskoop',
            'invoiceAddress.fullStreet'                                 => null,
            'invoiceAddress.number'                                     => null,
            'invoiceAddress.numberSuffix'                               => null,
            'invoiceAddress.postalCode'                                 => null,
            'invoiceAddress.region'                                     => null,
            'invoiceAddress.state'                                      => null,
            'invoiceAddress.street'                                     => null,
            'invoiceAddress.streetAdditionalInfo'                       => null,
            'invoiceAddress.email'                                      => null,
            'invoiceAddress.phone'                                      => null,
            'invoiceAddress.person'                                     => null,
            'invoiceAddress.company'                                    => null,
            'language'                                                  => null,
            'orderDate'                                                 => '2022-08-22 00:00:00',
            'orderLines.0.uuid'                                         => '1234',
            'orderLines.0.quantity'                                     => 1,
            'orderLines.0.price.amount'                                 => 250,
            'orderLines.0.price.currency'                               => 'EUR',
            'orderLines.0.vat.amount'                                   => 10,
            'orderLines.0.vat.currency'                                 => 'EUR',
            'orderLines.0.priceAfterVat.amount'                         => 260,
            'orderLines.0.priceAfterVat.currency'                       => 'EUR',
            'orderLines.0.product.uuid'                                 => '12345',
            'orderLines.0.product.sku'                                  => '018234',
            'orderLines.0.product.ean'                                  => '018234',
            'orderLines.0.product.externalIdentifier'                   => '018234',
            'orderLines.0.product.name'                                 => 'Paarse stofzuiger',
            'orderLines.0.product.description'                          => 'Een paars object waarmee stof opgezogen kan worden',
            'orderLines.0.product.width'                                => null,
            'orderLines.0.product.length'                               => null,
            'orderLines.0.product.height'                               => null,
            'orderLines.0.product.weight'                               => 3500,
            'price'                                                     => 260,
            'priceAfterVat'                                             => null,
            'shipment.id'                                               => null,
            'shipment.referenceIdentifier'                              => null,
            'shipment.externalIdentifier'                               => null,
            'shipment.apiKey'                                           => null,
            'shipment.barcode'                                          => null,
            'shipment.carrier'                                          => null,
            'shipment.collectionContact'                                => null,
            'shipment.created'                                          => null,
            'shipment.createdBy'                                        => null,
            'shipment.customsDeclaration'                               => null,
            'shipment.delayed'                                          => false,
            'shipment.delivered'                                        => false,
            'shipment.deliveryOptions.carrier'                          => 'Array',
            'shipment.deliveryOptions.date'                             => null,
            'shipment.deliveryOptions.deliveryType'                     => null,
            'shipment.deliveryOptions.packageType'                      => 'package',
            'shipment.deliveryOptions.pickupLocation'                   => null,
            'shipment.deliveryOptions.shipmentOptions.ageCheck'         => true,
            'shipment.deliveryOptions.shipmentOptions.insurance'        => 1,
            'shipment.deliveryOptions.shipmentOptions.labelDescription' => null,
            'shipment.deliveryOptions.shipmentOptions.largeFormat'      => false,
            'shipment.deliveryOptions.shipmentOptions.onlyRecipient'    => false,
            'shipment.deliveryOptions.shipmentOptions.return'           => false,
            'shipment.deliveryOptions.shipmentOptions.sameDayDelivery'  => false,
            'shipment.deliveryOptions.shipmentOptions.signature'        => true,
            'shipment.dropOffPoint'                                     => null,
            'shipment.isReturn'                                         => false,
            'shipment.linkConsumerPortal'                               => null,
            'shipment.modified'                                         => null,
            'shipment.modifiedBy'                                       => null,
            'shipment.multiCollo'                                       => false,
            'shipment.multiColloMainShipmentId'                         => null,
            'shipment.partnerTrackTraces'                               => null,
            'shipment.physicalProperties'                               => null,
            'shipment.recipient'                                        => null,
            'shipment.sender'                                           => null,
            'shipment.shopId'                                           => null,
            'shipment.status'                                           => null,
            'shipment.updated'                                          => null,
            'type'                                                      => null,
            'updatedAt'                                                 => null,
            'uuid'                                                      => null,
            'vat'                                                       => null,
        ],
    ],
]);
