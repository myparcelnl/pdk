<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

const INPUT_RECIPIENT = [
    'cc'         => 'NL',
    'city'       => 'Hoofddorp',
    'email'      => 'test@myparcel.nl',
    'person'     => 'Jaap Krekel',
    'postalCode' => '2132JE',
    'street'     => 'Antareslaan 31',
];

const DEFAULT_INPUT_SENDER = [
    'cc'         => 'NL',
    'city'       => 'Amsterdam',
    'number'     => '2',
    'person'     => 'Willem Wever',
    'postalCode' => '4164ZF',
    'street'     => 'Werf',
];

it('creates return shipment', function (array $input, array $output) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostIdsResponse());
    $mock->append(new ExampleGetShipmentsResponse());

    $repository             = $pdk->get(ShipmentRepository::class);
    $inputShipments         = (new Collection($input))->mapInto(Shipment::class);
    $createdReturnShipments = $repository->createReturnShipments(new ShipmentCollection($inputShipments->all()));
    $array                  = $createdReturnShipments->toArray();

    foreach ($array as $index => $shipment) {
        unset($shipment['carrier']['capabilities'], $shipment['carrier']['returnCapabilities']);
        $array[$index] = $shipment;
    }

    expect($createdReturnShipments)
        ->toBeInstanceOf(ShipmentCollection::class)
        ->and(Arr::dot($array))
        ->toEqual($output);
})->with([
    'simple domestic shipment' => [
        'input'  => [
            [
                'id'                  => 65435213,
                'carrier'             => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
                'deliveryOptions'     => [
                    'date'            => '2022-07-10 16:00:00',
                    'shipmentOptions' => [
                        'ageCheck'         => true,
                        'insurance'        => 500,
                        'labelDescription' => 'order 204829',
                        'largeFormat'      => false,
                        'onlyRecipient'    => true,
                        'return'           => false,
                        'sameDayDelivery'  => false,
                        'signature'        => false,
                    ],
                ],
                'physicalProperties'  => [
                    'height' => 100,
                    'width'  => 120,
                    'length' => 80,
                    'weight' => 2000,
                ],
                'recipient'           => INPUT_RECIPIENT,
                'referenceIdentifier' => 'Fulfilment-1',
                'sender'              => DEFAULT_INPUT_SENDER,
            ],
        ],
        'output' => [
            '0.id'                                               => 7,
            '0.referenceIdentifier'                              => 'GeheimeDingen-1',
            '0.externalIdentifier'                               => 'CV515676839NL',
            '0.apiKey'                                           => null,
            '0.barcode'                                          => 'CV515676839NL',
            '0.carrier.id'                                       => 1,
            '0.carrier.name'                                     => 'postnl',
            '0.carrier.human'                                    => null,
            '0.carrier.subscriptionId'                           => null,
            '0.carrier.primary'                                  => true,
            '0.carrier.isDefault'                                => null,
            '0.carrier.optional'                                 => null,
            '0.carrier.label'                                    => null,
            '0.carrier.type'                                     => 'main',
            '0.collectionContact'                                => null,
            '0.created'                                          => '2021-04-26 14:06:45',
            '0.createdBy'                                        => 35159,
            '0.customsDeclaration.contents'                      => 1,
            '0.customsDeclaration.invoice'                       => '123456',
            '0.customsDeclaration.items.0.amount'                => 1,
            '0.customsDeclaration.items.0.classification'        => '123456',
            '0.customsDeclaration.items.0.country'               => 'NL',
            '0.customsDeclaration.items.0.description'           => 'Product',
            '0.customsDeclaration.items.0.itemValue.amount'      => 1000,
            '0.customsDeclaration.items.0.itemValue.currency'    => 'EUR',
            '0.customsDeclaration.items.0.weight'                => 500,
            '0.customsDeclaration.weight'                        => 3500,
            '0.delayed'                                          => false,
            '0.delivered'                                        => false,
            '0.deliveryOptions.carrier'                          => 'postnl',
            '0.deliveryOptions.date'                             => null,
            '0.deliveryOptions.deliveryType'                     => '2',
            '0.deliveryOptions.labelAmount'                      => 1,
            '0.deliveryOptions.packageType'                      => '1',
            '0.deliveryOptions.pickupLocation'                   => null,
            '0.deliveryOptions.shipmentOptions.ageCheck'         => false,
            '0.deliveryOptions.shipmentOptions.insurance'        => 20000,
            '0.deliveryOptions.shipmentOptions.labelDescription' => 'standaard kenmerk',
            '0.deliveryOptions.shipmentOptions.largeFormat'      => false,
            '0.deliveryOptions.shipmentOptions.onlyRecipient'    => false,
            '0.deliveryOptions.shipmentOptions.return'           => false,
            '0.deliveryOptions.shipmentOptions.sameDayDelivery'  => null,
            '0.deliveryOptions.shipmentOptions.signature'        => false,
            '0.dropOffPoint'                                     => null,
            '0.isReturn'                                         => false,
            '0.linkConsumerPortal'                               => null,
            '0.modified'                                         => '2021-05-03 07:42:43',
            '0.modifiedBy'                                       => 35159,
            '0.multiCollo'                                       => false,
            '0.multiColloMainShipmentId'                         => null,
            '0.partnerTrackTraces'                               => [],
            '0.physicalProperties.height'                        => 20,
            '0.physicalProperties.length'                        => 40,
            '0.physicalProperties.weight'                        => 3500,
            '0.physicalProperties.width'                         => 30,
            '0.recipient.boxNumber'                              => null,
            '0.recipient.cc'                                     => 'CW',
            '0.recipient.city'                                   => 'Willemstad',
            '0.recipient.fullStreet'                             => null,
            '0.recipient.number'                                 => '12',
            '0.recipient.numberSuffix'                           => null,
            '0.recipient.postalCode'                             => null,
            '0.recipient.region'                                 => null,
            '0.recipient.state'                                  => null,
            '0.recipient.street'                                 => 'Schottegatweg Oost',
            '0.recipient.streetAdditionalInfo'                   => null,
            '0.recipient.email'                                  => 'meneer@groenteboer.nl',
            '0.recipient.phone'                                  => '+31699335577',
            '0.recipient.person'                                 => 'Joep Meloen',
            '0.recipient.company'                                => 'MyParcel',
            '0.sender.boxNumber'                                 => null,
            '0.sender.cc'                                        => 'NL',
            '0.sender.city'                                      => 'Hoofddorp',
            '0.sender.fullStreet'                                => null,
            '0.sender.number'                                    => '31',
            '0.sender.numberSuffix'                              => null,
            '0.sender.postalCode'                                => '2132 JE',
            '0.sender.region'                                    => null,
            '0.sender.state'                                     => null,
            '0.sender.street'                                    => 'Antareslaan',
            '0.sender.streetAdditionalInfo'                      => null,
            '0.sender.email'                                     => 'shop@geheimedingen.nl',
            '0.sender.phone'                                     => '0612345678',
            '0.sender.person'                                    => 'Denzel Crocker',
            '0.sender.company'                                   => 'Geheime Dingen',
            '0.shopId'                                           => 6,
            '0.orderId'                                          => null,
            '0.status'                                           => 2,
            '0.updated'                                          => null,

        ],
    ],
]);

it('creates a valid request from a shipment collection', function ($input, $path, $query, $contentType) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostIdsResponse());
    $mock->append(new ExampleGetShipmentsResponse());

    $repository = $pdk->get(ShipmentRepository::class);
    $response   = $repository->createReturnShipments(new ShipmentCollection($input));
    $request    = $mock->getLastRequest();

    if (! $request) {
        throw new RuntimeException('Request is not set');
    }

    $uri = $request->getUri();

    expect($uri->getQuery())
        ->toBe($query)
        ->and($uri->getPath())
        ->toBe($path)
        ->and($response)
        ->toBeInstanceOf(ShipmentCollection::class);
})->with([
    'single shipment' => [
        'input'       => [
            [
                'id'                  => 65435213,
                'carrier'             => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
                'deliveryOptions'     => [
                    'date'            => '2022-07-10 16:00:00',
                    'shipmentOptions' => [
                        'ageCheck'         => true,
                        'insurance'        => 500,
                        'labelDescription' => 'order 204829',
                        'largeFormat'      => false,
                        'onlyRecipient'    => true,
                        'return'           => false,
                        'sameDayDelivery'  => false,
                        'signature'        => false,
                    ],
                ],
                'physicalProperties'  => [
                    'height' => 100,
                    'width'  => 120,
                    'length' => 80,
                    'weight' => 2000,
                ],
                'recipient'           => INPUT_RECIPIENT,
                'referenceIdentifier' => 'my_ref_id',
                'sender'              => DEFAULT_INPUT_SENDER,
            ],
        ],
        'path'        => 'API/shipments/1',
        'query'       => '',
        'contentType' => 'application/vnd.return_shipment+json;charset=utf-8',
    ],
]);
