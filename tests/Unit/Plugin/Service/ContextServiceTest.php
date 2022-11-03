<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Service\ContextService;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
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
            'deliveryOptions'                                        => null,
        ],
    ],

    'empty order data' => [
        'id'          => Context::ID_ORDER_DATA,
        'arguments'   => [],
        'expectation' => [
            'global'          => null,
            'orderData'       => [],
            'deliveryOptions' => null,
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
            'orderData.0.deliveryOptions.carrier'                          => 'postnl',
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
            'orderData.0.lines'                                            => [],
            'orderData.0.recipient'                                        => null,
            'orderData.0.sender'                                           => null,
            'orderData.0.shipments'                                        => [],
            'orderData.0.shipmentPrice'                                    => null,
            'orderData.0.shipmentPriceAfterVat'                            => 0,
            'orderData.0.shipmentVat'                                      => null,
            'orderData.0.orderPrice'                                       => 0,
            'orderData.0.orderPriceAfterVat'                               => 0,
            'orderData.0.orderVat'                                         => 0,
            'orderData.0.physicalProperties.height'                        => null,
            'orderData.0.physicalProperties.length'                        => null,
            'orderData.0.physicalProperties.weight'                        => null,
            'orderData.0.physicalProperties.width'                         => null,
            'orderData.0.totalPrice'                                       => 0,
            'orderData.0.totalPriceAfterVat'                               => 0,
            'orderData.0.totalVat'                                         => 0,
            'deliveryOptions'                                              => null,
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
            'orderData.0.deliveryOptions.carrier'                          => 'postnl',
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
            'orderData.0.lines'                                            => [],
            'orderData.0.orderPrice'                                       => 0,
            'orderData.0.orderVat'                                         => 0,
            'orderData.0.orderPriceAfterVat'                               => 0,
            'orderData.0.shipmentPriceAfterVat'                            => 0,
            'orderData.0.totalPrice'                                       => 0,
            'orderData.0.totalVat'                                         => 0,
            'orderData.0.totalPriceAfterVat'                               => 0,
            'orderData.0.shipmentPrice'                                    => null,
            'orderData.0.shipmentVat'                                      => null,
            'orderData.0.recipient'                                        => null,
            'orderData.0.sender'                                           => null,
            'orderData.0.shipments'                                        => [],
            'orderData.0.physicalProperties.height'                        => null,
            'orderData.0.physicalProperties.length'                        => null,
            'orderData.0.physicalProperties.weight'                        => null,
            'orderData.0.physicalProperties.width'                         => null,
            'orderData.1.customsDeclaration.contents'                      => 1,
            'orderData.1.customsDeclaration.invoice'                       => null,
            'orderData.1.customsDeclaration.items'                         => [],
            'orderData.1.customsDeclaration.weight'                        => 0,
            'orderData.1.deliveryOptions.carrier'                          => 'postnl',
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
            'orderData.1.lines'                                            => [],
            'orderData.1.recipient'                                        => null,
            'orderData.1.sender'                                           => null,
            'orderData.1.shipments'                                        => [],
            'orderData.1.orderPrice'                                       => 0,
            'orderData.1.orderVat'                                         => 0,
            'orderData.1.orderPriceAfterVat'                               => 0,
            'orderData.1.shipmentPriceAfterVat'                            => 0,
            'orderData.1.totalPrice'                                       => 0,
            'orderData.1.totalVat'                                         => 0,
            'orderData.1.totalPriceAfterVat'                               => 0,
            'orderData.1.shipmentPrice'                                    => null,
            'orderData.1.shipmentVat'                                      => null,
            'orderData.1.physicalProperties.height'                        => null,
            'orderData.1.physicalProperties.length'                        => null,
            'orderData.1.physicalProperties.weight'                        => null,
            'orderData.1.physicalProperties.width'                         => null,
            'deliveryOptions'                                              => null,
        ],
    ],

    'delivery options config' => [
        'id'          => Context::ID_DELIVERY_OPTIONS,
        'arguments'   => [
            'order' => [
                'deliveryOptions' => [
                    'carrier'     => CarrierOptions::CARRIER_POSTNL_NAME,
                    'packageType' => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                ],
            ],
        ],
        'expectation' => [
            'deliveryOptions.config.apiBaseUrl'                                            => 'https://api.myparcel.nl',
            'deliveryOptions.config.basePrice'                                             => 0,
            'deliveryOptions.config.carrierSettings.instabox.allowDeliveryOptions'         => false,
            'deliveryOptions.config.carrierSettings.instabox.allowEveningDelivery'         => false,
            'deliveryOptions.config.carrierSettings.instabox.allowMondayDelivery'          => false,
            'deliveryOptions.config.carrierSettings.instabox.allowMorningDelivery'         => false,
            'deliveryOptions.config.carrierSettings.instabox.allowOnlyRecipient'           => false,
            'deliveryOptions.config.carrierSettings.instabox.allowPickupLocations'         => false,
            'deliveryOptions.config.carrierSettings.instabox.allowSameDayDelivery'         => true,
            'deliveryOptions.config.carrierSettings.instabox.allowSaturdayDelivery'        => false,
            'deliveryOptions.config.carrierSettings.instabox.allowShowDeliveryDate'        => true,
            'deliveryOptions.config.carrierSettings.instabox.allowSignature'               => false,
            'deliveryOptions.config.carrierSettings.instabox.defaultPackageType'           => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
            'deliveryOptions.config.carrierSettings.instabox.deliveryDaysWindow'           => 7,
            'deliveryOptions.config.carrierSettings.instabox.digitalStampDefaultWeight'    => 0,
            'deliveryOptions.config.carrierSettings.instabox.dropOffDelay'                 => 1,
            'deliveryOptions.config.carrierSettings.instabox.priceEveningDelivery'         => 0,
            'deliveryOptions.config.carrierSettings.instabox.priceMorningDelivery'         => 0,
            'deliveryOptions.config.carrierSettings.instabox.priceOnlyRecipient'           => 0,
            'deliveryOptions.config.carrierSettings.instabox.pricePackageTypeDigitalStamp' => 0,
            'deliveryOptions.config.carrierSettings.instabox.pricePackageTypeMailbox'      => 0,
            'deliveryOptions.config.carrierSettings.instabox.pricePickup'                  => 0,
            'deliveryOptions.config.carrierSettings.instabox.priceSameDayDelivery'         => 0,
            'deliveryOptions.config.carrierSettings.instabox.priceSignature'               => 0,
            'deliveryOptions.config.carrierSettings.instabox.priceStandardDelivery'        => 0,
            'deliveryOptions.config.carrierSettings.postnl.allowDeliveryOptions'           => false,
            'deliveryOptions.config.carrierSettings.postnl.allowEveningDelivery'           => false,
            'deliveryOptions.config.carrierSettings.postnl.allowMondayDelivery'            => false,
            'deliveryOptions.config.carrierSettings.postnl.allowMorningDelivery'           => false,
            'deliveryOptions.config.carrierSettings.postnl.allowOnlyRecipient'             => false,
            'deliveryOptions.config.carrierSettings.postnl.allowPickupLocations'           => false,
            'deliveryOptions.config.carrierSettings.postnl.allowSameDayDelivery'           => false,
            'deliveryOptions.config.carrierSettings.postnl.allowSaturdayDelivery'          => false,
            'deliveryOptions.config.carrierSettings.postnl.allowShowDeliveryDate'          => true,
            'deliveryOptions.config.carrierSettings.postnl.allowSignature'                 => true,
            'deliveryOptions.config.carrierSettings.postnl.cutoffTime'                     => '17:00',
            'deliveryOptions.config.carrierSettings.postnl.cutoffTimeSameDay'              => '10:00',
            'deliveryOptions.config.carrierSettings.postnl.defaultPackageType'             => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
            'deliveryOptions.config.carrierSettings.postnl.deliveryDaysWindow'             => 7,
            'deliveryOptions.config.carrierSettings.postnl.digitalStampDefaultWeight'      => 0,
            'deliveryOptions.config.carrierSettings.postnl.dropOffDays.0'                  => 1,
            'deliveryOptions.config.carrierSettings.postnl.dropOffDays.1'                  => 2,
            'deliveryOptions.config.carrierSettings.postnl.dropOffDays.2'                  => 3,
            'deliveryOptions.config.carrierSettings.postnl.dropOffDays.3'                  => 4,
            'deliveryOptions.config.carrierSettings.postnl.dropOffDays.4'                  => 5,
            'deliveryOptions.config.carrierSettings.postnl.dropOffDays.5'                  => 6,
            'deliveryOptions.config.carrierSettings.postnl.dropOffDelay'                   => 1,
            'deliveryOptions.config.carrierSettings.postnl.priceEveningDelivery'           => 0,
            'deliveryOptions.config.carrierSettings.postnl.priceMorningDelivery'           => 0,
            'deliveryOptions.config.carrierSettings.postnl.priceOnlyRecipient'             => 0,
            'deliveryOptions.config.carrierSettings.postnl.pricePackageTypeDigitalStamp'   => 0,
            'deliveryOptions.config.carrierSettings.postnl.pricePackageTypeMailbox'        => 0,
            'deliveryOptions.config.carrierSettings.postnl.pricePickup'                    => 0,
            'deliveryOptions.config.carrierSettings.postnl.priceSameDayDelivery'           => 0,
            'deliveryOptions.config.carrierSettings.postnl.priceSignature'                 => 80,
            'deliveryOptions.config.carrierSettings.postnl.priceStandardDelivery'          => 0,
            'deliveryOptions.config.carrierSettings.postnl.saturdayCutoffTime'             => '15:30',
            'deliveryOptions.config.currency'                                              => 'EUR',
            'deliveryOptions.config.locale'                                                => 'nl-NL',
            'deliveryOptions.config.packageType'                                           => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
            'deliveryOptions.config.pickupLocationsDefaultView'                            => CheckoutSettings::DEFAULT_PICKUP_LOCATIONS_VIEW,
            'deliveryOptions.config.platform'                                              => Platform::MYPARCEL_NAME,
            'deliveryOptions.config.priceStandardDelivery'                                 => 0,
            'deliveryOptions.config.showPriceSurcharge'                                    => null,
            'deliveryOptions.strings'                                                      => null,
            'global'                                                                       => null,
            'orderData'                                                                    => null,
        ],
    ],
]);

it('handles invalid context keys', function () {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Plugin\Service\ContextService $service */
    $service    = $pdk->get(ContextService::class);
    $contextBag = $service->createContexts(['random_word']);

    expect($contextBag->toArray())->toEqual([
        'global'          => null,
        'orderData'       => null,
        'deliveryOptions' => null,
    ]);
});
