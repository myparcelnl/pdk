<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

function setup(): void
{
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)->push(
                factory(Carrier::class)->fromPOSTNL(),
                factory(Carrier::class)
                    ->fromDhlForYou()
                    ->withContractId(12345)
            )
        )
        ->store();

    factory(Settings::class)
        ->withCarrierPostNl(
            factory(CarrierSettings::class)
                ->withExportAgeCheck(true)
                ->withExportSignature(true)
        )
        ->withCarrier(
            'DHL_FOR_YOU',
            factory(CarrierSettings::class)->withExportOnlyRecipient(true)
        )
        ->store();

    factory(PdkProduct::class)
        ->withExternalIdentifier('product-1')
        ->withWeight(120)
        ->withSettings(
            factory(ProductSettings::class)
                ->withExportAgeCheck(TriStateService::DISABLED)
                ->withExportLargeFormat(TriStateService::ENABLED)
        )
        ->store();
}

it('exposes inherited options', function () {
    setup();

    $order = factory(PdkOrder::class)
        ->withLines([factory(PdkOrderLine::class)->withProduct('product-1')])
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withShipmentOptions(factory(ShipmentOptions::class)->withReturn(TriStateService::ENABLED))
        )
        ->make();

    $context = factory(OrderDataContext::class)
        ->with($order->getAttributes())
        ->make();

    expect($context->deliveryOptions['shipmentOptions'])
        ->toEqual([
            'insurance'                   => TriStateService::INHERIT,
            'requiresAgeVerification'     => TriStateService::INHERIT,
            'hideSender'                  => TriStateService::INHERIT,
            'oversizedPackage'            => TriStateService::INHERIT,
            'recipientOnlyDelivery'       => TriStateService::INHERIT,
            'priorityDelivery'            => TriStateService::INHERIT,
            'returnOnFirstFailedDelivery' => TriStateService::ENABLED,
            'sameDayDelivery'             => TriStateService::INHERIT,
            'requiresSignature'           => TriStateService::INHERIT,
            'tracked'                     => TriStateService::INHERIT,
            'requiresReceiptCode'         => TriStateService::INHERIT,
            'scheduledCollection'         => TriStateService::INHERIT,
            'freshFood'                   => TriStateService::INHERIT,
            'frozen'                      => TriStateService::INHERIT,
            'saturdayDelivery'            => TriStateService::INHERIT,
        ])
        ->and($context->inheritedDeliveryOptions->toArrayWithoutNull())
        ->toEqual([
            'POSTNL'      => [
                DeliveryOptions::LABEL_AMOUNT     => 1,
                DeliveryOptions::DELIVERY_TYPE    => RefTypesDeliveryTypeV2::STANDARD,
                DeliveryOptions::PACKAGE_TYPE     => RefShipmentPackageTypeV2::PACKAGE,
                DeliveryOptions::SHIPMENT_OPTIONS => [
                    'insurance'                   => TriStateService::DISABLED,
                    // Enabled by carrier, but disabled by product
                    'requiresAgeVerification'     => TriStateService::DISABLED,
                    'hideSender'                  => TriStateService::DISABLED,
                    // Enabled by product
                    'oversizedPackage'            => TriStateService::ENABLED,
                    'recipientOnlyDelivery'       => TriStateService::DISABLED,
                    'priorityDelivery'            => TriStateService::DISABLED,
                    'returnOnFirstFailedDelivery' => TriStateService::DISABLED,
                    'sameDayDelivery'             => TriStateService::DISABLED,
                    // Enabled by carrier
                    'requiresSignature'           => TriStateService::ENABLED,
                    // Disabled by default
                    'tracked'                     => TriStateService::DISABLED,
                    'requiresReceiptCode'         => TriStateService::DISABLED,
                    'scheduledCollection'         => TriStateService::DISABLED,
                    'freshFood'                   => TriStateService::DISABLED,
                    'frozen'                      => TriStateService::DISABLED,
                    'saturdayDelivery'            => TriStateService::DISABLED,
                ],
            ],
            'DHL_FOR_YOU' => [
                DeliveryOptions::LABEL_AMOUNT     => 1,
                DeliveryOptions::DELIVERY_TYPE    => RefTypesDeliveryTypeV2::STANDARD,
                DeliveryOptions::PACKAGE_TYPE     => RefShipmentPackageTypeV2::PACKAGE,
                DeliveryOptions::SHIPMENT_OPTIONS => [
                    'insurance'                   => TriStateService::DISABLED,
                    'requiresAgeVerification'     => TriStateService::DISABLED,
                    'hideSender'                  => TriStateService::DISABLED,
                    // Enabled by product
                    'oversizedPackage'            => TriStateService::ENABLED,
                    // Enabled by carrier
                    'recipientOnlyDelivery'       => TriStateService::ENABLED,
                    // Disabled by default
                    'priorityDelivery'            => TriStateService::DISABLED,
                    'returnOnFirstFailedDelivery' => TriStateService::DISABLED,
                    'sameDayDelivery'             => TriStateService::DISABLED,
                    'requiresSignature'           => TriStateService::DISABLED,
                    'tracked'                     => TriStateService::DISABLED,
                    'requiresReceiptCode'         => TriStateService::DISABLED,
                    'scheduledCollection'         => TriStateService::DISABLED,
                    'freshFood'                   => TriStateService::DISABLED,
                    'frozen'                      => TriStateService::DISABLED,
                    'saturdayDelivery'            => TriStateService::DISABLED,
                ],
            ],
        ]);
});

it('produces correct v2 delivery type through full context service flow', function () {
    /** @var \MyParcelNL\Pdk\Context\Service\ContextService $service */
    $service = \MyParcelNL\Pdk\Facade\Pdk::get(\MyParcelNL\Pdk\Context\Contract\ContextServiceInterface::class);

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
        )
        ->make();

    $context = $service->createContexts([\MyParcelNL\Pdk\Context\Context::ID_ORDER_DATA], ['order' => $order]);
    $array   = $context->toArrayWithoutNull();

    expect($array['orderData'][0]['deliveryOptions']['deliveryType'])->toBe(RefTypesDeliveryTypeV2::EVENING)
        ->and($array['orderData'][0]['deliveryOptions']['packageType'])->toBe(RefShipmentPackageTypeV2::MAILBOX);
});

it('converts delivery type to v2 when order was filled with a DeliveryOptions object (post-updateOrders flow)', function () {
    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
        )
        ->make();

    // Simulate AbstractOrderAction::updateOrders: fill the order with a DeliveryOptions object (not an array)
    $deliveryOptionsObject = DeliveryOptions::fromCapabilitiesDefinitions(array_merge(
        $order->deliveryOptions->toArray(),
        ['deliveryType' => RefTypesDeliveryTypeV2::EVENING, 'packageType' => RefShipmentPackageTypeV2::MAILBOX]
    ));
    $order->fill(['deliveryOptions' => $deliveryOptionsObject]);

    // Now the raw attributes contain a DeliveryOptions object, not an array
    $context = factory(OrderDataContext::class)
        ->with($order->getAttributes())
        ->make();

    expect($context->deliveryOptions['deliveryType'])->toBe(RefTypesDeliveryTypeV2::EVENING)
        ->and($context->deliveryOptions['packageType'])->toBe(RefShipmentPackageTypeV2::MAILBOX);
});

it('converts stored v1 delivery type to v2 in delivery options', function (string $storedDeliveryType, string $expectedV2DeliveryType) {
    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withDeliveryType($storedDeliveryType)
        )
        ->make();

    $context = factory(OrderDataContext::class)
        ->with($order->getAttributes())
        ->make();

    expect($context->deliveryOptions['deliveryType'])->toBe($expectedV2DeliveryType);
})->with([
    'evening'  => [DeliveryOptions::DELIVERY_TYPE_EVENING_NAME, RefTypesDeliveryTypeV2::EVENING],
    'morning'  => [DeliveryOptions::DELIVERY_TYPE_MORNING_NAME, RefTypesDeliveryTypeV2::MORNING],
    'standard' => [DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME, RefTypesDeliveryTypeV2::STANDARD],
]);

it('converts stored v1 package type to v2 in delivery options', function (string $storedPackageType, string $expectedV2PackageType) {
    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withPackageType($storedPackageType)
        )
        ->make();

    $context = factory(OrderDataContext::class)
        ->with($order->getAttributes())
        ->make();

    expect($context->deliveryOptions['packageType'])->toBe($expectedV2PackageType);
})->with([
    'package'  => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME, RefShipmentPackageTypeV2::PACKAGE],
    'mailbox'  => [DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME, RefShipmentPackageTypeV2::MAILBOX],
]);

it('gets digital stamp ranges', function () {
    $context = factory(OrderDataContext::class)->make();

    expect($context->digitalStampRanges)
        ->toBeArray()
        ->and($context->digitalStampRanges)->not->toBeEmpty()
        ->and($context->digitalStampRanges)->each->toBeArray()
        ->and($context->digitalStampRanges)->each->toHaveKeys(['min', 'max', 'average']);
});
