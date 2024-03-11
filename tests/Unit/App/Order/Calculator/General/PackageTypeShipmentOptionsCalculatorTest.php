<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Order\Service\PdkOrderOptionsService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it(
    'disables shipment options if package type is not package',
    function (string $carrierName) {
        $reset = mockPdkProperty('orderCalculators', [PackageTypeShipmentOptionsCalculator::class]);

        $order = factory(PdkOrder::class)
            ->withDeliveryOptions(
                factory(DeliveryOptions::class)
                    ->withCarrier($carrierName)
                    ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
                    ->withAllShipmentOptions()
            )
            ->make();

        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
        $service  = Pdk::get(PdkOrderOptionsService::class);
        $newOrder = $service->calculate($order);

        expect($newOrder->deliveryOptions->shipmentOptions->toArray())->toHaveKeysAndValues([
            ShipmentOptions::AGE_CHECK         => TriStateService::DISABLED,
            ShipmentOptions::DIRECT_RETURN     => TriStateService::DISABLED,
            ShipmentOptions::HIDE_SENDER       => TriStateService::DISABLED,
            ShipmentOptions::LARGE_FORMAT      => TriStateService::DISABLED,
            ShipmentOptions::ONLY_RECIPIENT    => TriStateService::DISABLED,
            ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::DISABLED,
            ShipmentOptions::SIGNATURE         => TriStateService::DISABLED,
        ]);

        $reset();
    }
)->with('carrierNames');

it(
    'allows tracked only for international package small',
    function (string $countryCode, bool $expected) {
        $reset = mockPdkProperty('orderCalculators', [PackageTypeShipmentOptionsCalculator::class]);

        $order = factory(PdkOrder::class)
            ->withShippingAddress(factory(ShippingAddress::class)->withCc($countryCode))
            ->withDeliveryOptions(
                factory(DeliveryOptions::class)
                    ->withCarrier('postnl')
                    ->withPackageType(DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME)
            )
            ->make();

        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
        $service  = Pdk::get(PdkOrderOptionsService::class);
        $newOrder = $service->calculate($order);

        expect($newOrder->deliveryOptions->shipmentOptions->toArray())->toHaveKeysAndValues([
            ShipmentOptions::TRACKED => $expected ? TriStateService::ENABLED : TriStateService::DISABLED,
        ]);

        $reset();
    }
)->with([['cc' => 'NL' ,'expected'=> false], ['cc' => 'BE', 'expected' => true], ['cc' => 'DE', 'expected' => true]]);
