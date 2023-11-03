<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Order\Service\PdkOrderOptionsService;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('handles customer information', function (bool $shareCustomerInfo, bool $needsCustomerInfo) {
    factory(OrderSettings::class)
        ->withShareCustomerInformation($shareCustomerInfo)
        ->store();

    $reset = mockPdkProperty('orderCalculators', [CustomerInformationCalculator::class]);

    $fakeCarrier = factory(Carrier::class)
        ->fromPostNL()
        ->withCapabilities(['features' => ['needsCustomerInfo' => $needsCustomerInfo]]);

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($fakeCarrier))
        ->withShippingAddress(
            factory(ShippingAddress::class)
                ->inTheNetherlands()
                ->withEmail('hello@myparcel.nl')
                ->withPhone('123456789')
        )
        ->withBillingAddress(
            factory(ContactDetails::class)
                ->inTheNetherlands()
                ->withEmail('bye@myparcel.nl')
                ->withPhone('987654321')
        )
        ->make();

    $order->shipments->push($order->createShipment());

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsService::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->shipments)->toHaveLength(1);

    if ($shareCustomerInfo || $needsCustomerInfo) {
        expect($newOrder->shippingAddress->email)
            ->toBe('hello@myparcel.nl')
            ->and($newOrder->shippingAddress->phone)
            ->toBe('123456789')
            ->and($newOrder->billingAddress->email)
            ->toBe('bye@myparcel.nl')
            ->and($newOrder->billingAddress->phone)
            ->toBe('987654321');

        $newOrder->shipments->each(function (Shipment $shipment) {
            expect($shipment->recipient->email)
                ->toBe('hello@myparcel.nl')
                ->and($shipment->recipient->phone)
                ->toBe('123456789');
        });
    } else {
        expect($newOrder->shippingAddress->email)
            ->toBeNull()
            ->and($newOrder->shippingAddress->phone)
            ->toBeNull()
            ->and($newOrder->billingAddress->email)
            ->toBeNull()
            ->and($newOrder->billingAddress->phone)
            ->toBeNull();

        $newOrder->shipments->each(function (Shipment $shipment) {
            expect($shipment->recipient->email)
                ->toBeNull()
                ->and($shipment->recipient->phone)
                ->toBeNull();
        });
    }

    $reset();
})
    ->with([
        'share customer information'        => [true],
        'do not share customer information' => [false],
    ])
    ->with([
        'carrier that does not need customer info' => [false],
        'carrier that needs customer info'         => [true],
    ]);
