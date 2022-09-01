<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
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
        ->toEqual($output);
})->with([
    'normal shipment' => [
        'response' => ExampleGetOrdersResponse::class,
        'output'   => [
            'accountId'                                                 => null,
            'createdAt'                                                 => null,
            'externalIdentifier'                                        => null,
            'fulfilmentPartnerIdentifier'                               => null,
            'invoiceAddress.boxNumber'                                  => null,
            'invoiceAddress.cc'                                         => 'NL',
            'invoiceAddress.city'                                       => 'Boskoop',
            'invoiceAddress.company'                                    => null,
            'invoiceAddress.email'                                      => null,
            'invoiceAddress.fullStreet'                                 => null,
            'invoiceAddress.number'                                     => null,
            'invoiceAddress.numberSuffix'                               => null,
            'invoiceAddress.person'                                     => null,
            'invoiceAddress.phone'                                      => null,
            'invoiceAddress.postalCode'                                 => null,
            'invoiceAddress.region'                                     => null,
            'invoiceAddress.state'                                      => null,
            'invoiceAddress.street'                                     => null,
            'invoiceAddress.streetAdditionalInfo'                       => null,
            'language'                                                  => null,
            'orderDate'                                                 => '2022-08-22 00:00:00',
            'orderLines.0.price'                                        => 250,
            'orderLines.0.priceAfterVat'                                => 260,
            'orderLines.0.product.description'                          => 'Een paars object waarmee stof opgezogen kan worden',
            'orderLines.0.product.ean'                                  => '018234',
            'orderLines.0.product.externalIdentifier'                   => '018234',
            'orderLines.0.product.height'                               => null,
            'orderLines.0.product.length'                               => null,
            'orderLines.0.product.name'                                 => 'Paarse stofzuiger',
            'orderLines.0.product.sku'                                  => '018234',
            'orderLines.0.product.uuid'                                 => '12345',
            'orderLines.0.product.weight'                               => 3500,
            'orderLines.0.product.width'                                => null,
            'orderLines.0.quantity'                                     => 1,
            'orderLines.0.uuid'                                         => '1234',
            'orderLines.0.vat'                                          => 10,
            'price'                                                     => 260,
            'priceAfterVat'                                             => null,
            'shipment.apiKey'                                           => null,
            'shipment.barcode'                                          => null,
            'shipment.carrier.human'                                    => null,
            'shipment.carrier.id'                                       => CarrierOptions::CARRIER_POSTNL_ID,
            'shipment.carrier.isDefault'                                => null,
            'shipment.carrier.label'                                    => null,
            'shipment.carrier.name'                                     => 'postnl',
            'shipment.carrier.optional'                                 => null,
            'shipment.carrier.primary'                                  => true,
            'shipment.carrier.subscriptionId'                           => null,
            'shipment.carrier.type'                                     => 'main',
            'shipment.collectionContact'                                => null,
            'shipment.created'                                          => null,
            'shipment.createdBy'                                        => null,
            'shipment.customsDeclaration'                               => null,
            'shipment.delayed'                                          => null,
            'shipment.delivered'                                        => null,
            'shipment.deliveryOptions.carrier'                          => CarrierOptions::CARRIER_POSTNL_NAME,
            'shipment.deliveryOptions.date'                             => null,
            'shipment.deliveryOptions.deliveryType'                     => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            'shipment.deliveryOptions.labelAmount'                      => 1,
            'shipment.deliveryOptions.packageType'                      => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'shipment.deliveryOptions.pickupLocation'                   => null,
            'shipment.deliveryOptions.shipmentOptions.ageCheck'         => true,
            'shipment.deliveryOptions.shipmentOptions.insurance'        => 0,
            'shipment.deliveryOptions.shipmentOptions.labelDescription' => null,
            'shipment.deliveryOptions.shipmentOptions.largeFormat'      => false,
            'shipment.deliveryOptions.shipmentOptions.onlyRecipient'    => false,
            'shipment.deliveryOptions.shipmentOptions.return'           => false,
            'shipment.deliveryOptions.shipmentOptions.sameDayDelivery'  => false,
            'shipment.deliveryOptions.shipmentOptions.signature'        => true,
            'shipment.dropOffPoint'                                     => null,
            'shipment.externalIdentifier'                               => null,
            'shipment.id'                                               => null,
            'shipment.isReturn'                                         => false,
            'shipment.linkConsumerPortal'                               => null,
            'shipment.modified'                                         => null,
            'shipment.modifiedBy'                                       => null,
            'shipment.multiCollo'                                       => false,
            'shipment.multiColloMainShipmentId'                         => null,
            'shipment.orderId'                                          => null,
            'shipment.partnerTrackTraces'                               => null,
            'shipment.physicalProperties'                               => null,
            'shipment.recipient.boxNumber'                              => null,
            'shipment.recipient.cc'                                     => 'NL',
            'shipment.recipient.city'                                   => 'Hoofddorp',
            'shipment.recipient.company'                                => null,
            'shipment.recipient.email'                                  => null,
            'shipment.recipient.fullStreet'                             => null,
            'shipment.recipient.number'                                 => null,
            'shipment.recipient.numberSuffix'                           => null,
            'shipment.recipient.person'                                 => 'Jaap Krekel',
            'shipment.recipient.phone'                                  => null,
            'shipment.recipient.postalCode'                             => '2132JE',
            'shipment.recipient.region'                                 => null,
            'shipment.recipient.state'                                  => null,
            'shipment.recipient.street'                                 => 'Antareslaan 31',
            'shipment.recipient.streetAdditionalInfo'                   => null,
            'shipment.referenceIdentifier'                              => null,
            'shipment.sender.boxNumber'                                 => null,
            'shipment.sender.cc'                                        => 'NL',
            'shipment.sender.city'                                      => 'Amsterdam',
            'shipment.sender.company'                                   => null,
            'shipment.sender.email'                                     => null,
            'shipment.sender.fullStreet'                                => null,
            'shipment.sender.number'                                    => '2',
            'shipment.sender.numberSuffix'                              => null,
            'shipment.sender.person'                                    => 'Willem Wever',
            'shipment.sender.phone'                                     => null,
            'shipment.sender.postalCode'                                => '4164ZF',
            'shipment.sender.region'                                    => null,
            'shipment.sender.state'                                     => null,
            'shipment.sender.street'                                    => 'Werf',
            'shipment.sender.streetAdditionalInfo'                      => null,
            'shipment.shopId'                                           => null,
            'shipment.status'                                           => null,
            'shipment.updated'                                          => null,
            'shopId'                                                    => null,
            'status'                                                    => null,
            'type'                                                      => null,
            'updatedAt'                                                 => null,
            'uuid'                                                      => null,
            'vat'                                                       => null,
        ],
    ],
]);
