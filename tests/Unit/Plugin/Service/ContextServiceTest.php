<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Service\ContextService;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

it('gets context data', function (string $id, array $arguments, array $expectation) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Plugin\Service\ContextService $service */
    $service = $pdk->get(ContextService::class);

    $context = $service->createContexts([$id], $arguments);

    expect(Arr::dot($context->toArray()))->toEqual($expectation);
})->with([
    'global' => [
        'id'          => Context::ID_GLOBAL,
        'arguments'   => [],
        'expectation' => [
            'global.baseUrl'                                         => 'CMS_URL',
            'global.bootstrapId'                                     => 'myparcel-pdk-bootstrap',
            'global.endpoints.exportAndPrintOrder.body'              => null,
            'global.endpoints.exportAndPrintOrder.headers'           => [],
            'global.endpoints.exportAndPrintOrder.method'            => 'POST',
            'global.endpoints.exportAndPrintOrder.parameters.action' => 'exportAndPrintOrder',
            'global.endpoints.exportAndPrintOrder.path'              => '',
            'global.endpoints.exportOrder.body'                      => null,
            'global.endpoints.exportOrder.headers'                   => [],
            'global.endpoints.exportOrder.method'                    => 'POST',
            'global.endpoints.exportOrder.parameters.action'         => 'exportOrder',
            'global.endpoints.exportOrder.path'                      => '',
            'global.endpoints.getOrderData.body'                     => null,
            'global.endpoints.getOrderData.headers'                  => [],
            'global.endpoints.getOrderData.method'                   => 'GET',
            'global.endpoints.getOrderData.parameters.action'        => 'getOrderData',
            'global.endpoints.getOrderData.path'                     => '',
            'global.event'                                           => 'myparcel_pdk_loaded',
            'global.mode'                                            => 'production',
            'global.pluginSettings'                                  => [],
            'global.translations.apple_tree'                         => 'Appelboom',
            'orderData'                                              => null,
        ],
    ],

    'empty order data' => [
        'id'          => Context::ID_ORDER_DATA,
        'arguments'   => [],
        'expectation' => [
            'global'    => null,
            'orderData' => [],
        ],
    ],

    'single order' => [
        'id'          => Context::ID_ORDER_DATA,
        'arguments'   => [
            'order' => [
                'externalIdentifier' => '123',
            ],
        ],
        'expectation' => [
            'global'                                                       => null,
            'orderData.0.externalIdentifier'                               => '123',
            'orderData.0.customsDeclaration.contents'                      => 1,
            'orderData.0.customsDeclaration.invoice'                       => null,
            'orderData.0.customsDeclaration.items'                         => [],
            'orderData.0.customsDeclaration.weight'                        => 0,
            'orderData.0.deliveryOptions.carrier'                          => null,
            'orderData.0.deliveryOptions.date'                             => null,
            'orderData.0.deliveryOptions.deliveryType'                     => DeliveryOptions::DEFAULT_DELIVERY_TYPE_NAME,
            'orderData.0.deliveryOptions.labelAmount'                      => 1,
            'orderData.0.deliveryOptions.packageType'                      => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
            'orderData.0.deliveryOptions.pickupLocation'                   => null,
            'orderData.0.deliveryOptions.shipmentOptions.ageCheck'         => null,
            'orderData.0.deliveryOptions.shipmentOptions.insurance'        => null,
            'orderData.0.deliveryOptions.shipmentOptions.labelDescription' => null,
            'orderData.0.deliveryOptions.shipmentOptions.largeFormat'      => null,
            'orderData.0.deliveryOptions.shipmentOptions.onlyRecipient'    => null,
            'orderData.0.deliveryOptions.shipmentOptions.return'           => null,
            'orderData.0.deliveryOptions.shipmentOptions.sameDayDelivery'  => null,
            'orderData.0.deliveryOptions.shipmentOptions.signature'        => null,
            'orderData.0.label'                                            => null,
            'orderData.0.recipient'                                        => null,
            'orderData.0.sender'                                           => null,
            'orderData.0.shipments'                                        => [],
        ],
    ],

    'multiple orders' => [
        'id'          => Context::ID_ORDER_DATA,
        'arguments'   => [
            'order' => [
                [
                    'externalIdentifier' => '123',
                ],
                [
                    'externalIdentifier' => '124',
                ],
            ],
        ],
        'expectation' => [
            'global'                                                       => null,
            'orderData.0.customsDeclaration.contents'                      => 1,
            'orderData.0.customsDeclaration.invoice'                       => null,
            'orderData.0.customsDeclaration.items'                         => [],
            'orderData.0.customsDeclaration.weight'                        => 0,
            'orderData.0.deliveryOptions.carrier'                          => null,
            'orderData.0.deliveryOptions.date'                             => null,
            'orderData.0.deliveryOptions.deliveryType'                     => DeliveryOptions::DEFAULT_DELIVERY_TYPE_NAME,
            'orderData.0.deliveryOptions.labelAmount'                      => 1,
            'orderData.0.deliveryOptions.packageType'                      => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
            'orderData.0.deliveryOptions.pickupLocation'                   => null,
            'orderData.0.deliveryOptions.shipmentOptions.ageCheck'         => null,
            'orderData.0.deliveryOptions.shipmentOptions.insurance'        => null,
            'orderData.0.deliveryOptions.shipmentOptions.labelDescription' => null,
            'orderData.0.deliveryOptions.shipmentOptions.largeFormat'      => null,
            'orderData.0.deliveryOptions.shipmentOptions.onlyRecipient'    => null,
            'orderData.0.deliveryOptions.shipmentOptions.return'           => null,
            'orderData.0.deliveryOptions.shipmentOptions.sameDayDelivery'  => null,
            'orderData.0.deliveryOptions.shipmentOptions.signature'        => null,
            'orderData.0.externalIdentifier'                               => '123',
            'orderData.0.label'                                            => null,
            'orderData.0.recipient'                                        => null,
            'orderData.0.sender'                                           => null,
            'orderData.0.shipments'                                        => [],
            'orderData.1.customsDeclaration.contents'                      => 1,
            'orderData.1.customsDeclaration.invoice'                       => null,
            'orderData.1.customsDeclaration.items'                         => [],
            'orderData.1.customsDeclaration.weight'                        => 0,
            'orderData.1.deliveryOptions.carrier'                          => null,
            'orderData.1.deliveryOptions.date'                             => null,
            'orderData.1.deliveryOptions.deliveryType'                     => DeliveryOptions::DEFAULT_DELIVERY_TYPE_NAME,
            'orderData.1.deliveryOptions.labelAmount'                      => 1,
            'orderData.1.deliveryOptions.packageType'                      => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
            'orderData.1.deliveryOptions.pickupLocation'                   => null,
            'orderData.1.deliveryOptions.shipmentOptions.ageCheck'         => null,
            'orderData.1.deliveryOptions.shipmentOptions.insurance'        => null,
            'orderData.1.deliveryOptions.shipmentOptions.labelDescription' => null,
            'orderData.1.deliveryOptions.shipmentOptions.largeFormat'      => null,
            'orderData.1.deliveryOptions.shipmentOptions.onlyRecipient'    => null,
            'orderData.1.deliveryOptions.shipmentOptions.return'           => null,
            'orderData.1.deliveryOptions.shipmentOptions.sameDayDelivery'  => null,
            'orderData.1.deliveryOptions.shipmentOptions.signature'        => null,
            'orderData.1.externalIdentifier'                               => '124',
            'orderData.1.label'                                            => null,
            'orderData.1.recipient'                                        => null,
            'orderData.1.sender'                                           => null,
            'orderData.1.shipments'                                        => [],
        ],
    ],
]);

it('handles invalid context keys', function () {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Plugin\Service\ContextService $service */
    $service    = $pdk->get(ContextService::class);
    $contextBag = $service->createContexts(['random_word']);

    expect($contextBag->toArray())->toEqual([
        'global'    => null,
        'orderData' => null,
    ]);
});
