<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\PostNl;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Base\Service\CountryCodes;

usesShared(new UsesMockPdkInstance());

it('disables signature, only recipient, large format and return when receipt code is enabled', function () {
    $reset = mockPdkProperty('orderCalculators', [PostNLReceiptCodeCalculator::class]);

    $order = factory(PdkOrder::class)
        ->withShippingAddress(['cc' => 'NL'])
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)
                        ->withSignature(TriStateService::ENABLED)
                        ->withOnlyRecipient(TriStateService::ENABLED)
                        ->withLargeFormat(TriStateService::ENABLED)
                        ->withReturn(TriStateService::ENABLED)
                        ->withReceiptCode(TriStateService::ENABLED)
                )
        )
        ->make();

    $calculator = new PostNLReceiptCodeCalculator($order);
    $calculator->calculate();

    $shipmentOptions = $order->deliveryOptions->shipmentOptions;

    expect($shipmentOptions->signature)
        ->toBe(TriStateService::DISABLED)
        ->and($shipmentOptions->onlyRecipient)
        ->toBe(TriStateService::DISABLED)
        ->and($shipmentOptions->largeFormat)
        ->toBe(TriStateService::DISABLED)
        ->and($shipmentOptions->return)
        ->toBe(TriStateService::DISABLED);

    $reset();
});

it('sets minimum insurance when receipt code is enabled and insurance is not set', function () {
    $reset = mockPdkProperty('orderCalculators', [PostNLReceiptCodeCalculator::class]);

    $carrier = factory(Carrier::class)
        ->withName(Carrier::CARRIER_POSTNL_NAME)
        ->withCapabilities(
            factory(CarrierCapabilities::class)->withShipmentOptions(['insurance' => [5000, 10000, 25000]])
        )
        ->make();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(['cc' => CountryCodes::CC_NL])
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)
                        ->withReceiptCode(TriStateService::ENABLED)
                        ->withInsurance(0)
                )
        )
        ->make();

    $calculator = new PostNLReceiptCodeCalculator($order);
    $calculator->calculate();

    expect($order->deliveryOptions->shipmentOptions->insurance)->toBe(5000);

    $reset();
});

it('does not change insurance when receipt code is enabled and insurance is already set', function () {
    $reset = mockPdkProperty('orderCalculators', [PostNLReceiptCodeCalculator::class]);

    $carrier = factory(Carrier::class)
        ->withName(Carrier::CARRIER_POSTNL_NAME)
        ->withCapabilities(
            factory(CarrierCapabilities::class)->withShipmentOptions(['insurance' => [5000, 10000, 25000]])
        )
        ->make();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(['cc' => CountryCodes::CC_NL])
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)
                        ->withReceiptCode(TriStateService::ENABLED)
                        ->withInsurance(10000)
                )
        )
        ->make();

    $calculator = new PostNLReceiptCodeCalculator($order);
    $calculator->calculate();

    expect($order->deliveryOptions->shipmentOptions->insurance)->toBe(10000);

    $reset();
});

it('does nothing when receipt code is disabled', function () {
    $reset = mockPdkProperty('orderCalculators', [PostNLReceiptCodeCalculator::class]);

    $order = factory(PdkOrder::class)
        ->withShippingAddress(['cc' => CountryCodes::CC_NL])
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)
                        ->withReceiptCode(TriStateService::DISABLED)
                        ->withSignature(TriStateService::ENABLED)
                        ->withOnlyRecipient(TriStateService::ENABLED)
                        ->withLargeFormat(TriStateService::ENABLED)
                        ->withReturn(TriStateService::ENABLED)
                )
        )
        ->make();

    $calculator = new PostNLReceiptCodeCalculator($order);
    $calculator->calculate();

    $shipmentOptions = $order->deliveryOptions->shipmentOptions;

    expect($shipmentOptions->signature)
        ->toBe(TriStateService::ENABLED)
        ->and($shipmentOptions->onlyRecipient)
        ->toBe(TriStateService::ENABLED)
        ->and($shipmentOptions->largeFormat)
        ->toBe(TriStateService::ENABLED)
        ->and($shipmentOptions->return)
        ->toBe(TriStateService::ENABLED);

    $reset();
});

it('returns 0 when no valid insurance amounts are available', function () {
    $reset = mockPdkProperty('orderCalculators', [PostNLReceiptCodeCalculator::class]);

    $carrier = factory(Carrier::class)
        ->withName(Carrier::CARRIER_POSTNL_NAME)
        ->withCapabilities(
            factory(CarrierCapabilities::class)->withShipmentOptions(['insurance' => [0]])
        )
        ->make();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(['cc' => CountryCodes::CC_NL])
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)
                        ->withReceiptCode(TriStateService::ENABLED)
                        ->withInsurance(TriStateService::DISABLED)
                )
        )
        ->make();

    $calculator = new PostNLReceiptCodeCalculator($order);
    $calculator->calculate();

    expect($order->deliveryOptions->shipmentOptions->insurance)->toBe(0);

    $reset();
});
