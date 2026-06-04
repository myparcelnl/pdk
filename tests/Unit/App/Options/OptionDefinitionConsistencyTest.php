<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Model\ShipmentOptions as FulfilmentShipmentOptions;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

/**
 * Proves all real registered definitions are correctly wired into the real models.
 * Unlike the flow test (which uses a fake definition to test the mechanism), this test
 * iterates all 17+ production definitions and verifies each one produces valid attributes
 * with correct defaults on CarrierSettings, ProductSettings, ShipmentOptions, and
 * Fulfilment\ShipmentOptions.
 */

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('registers all definition-derived attributes on CarrierSettings', function () {
    $definitions     = Pdk::get('orderOptionDefinitions');
    $carrierSettings = new CarrierSettings();
    $attributes      = $carrierSettings->getAttributes();

    foreach ($definitions as $definition) {
        $carrierKey = $definition->getCarrierSettingsKey();
        if ($carrierKey !== null) {
            expect(array_key_exists($carrierKey, $attributes))
                ->toBeTrue("CarrierSettings is missing attribute '{$carrierKey}' for " . get_class($definition));
        }

        $allowKey = $definition->getAllowSettingsKey();
        if ($allowKey !== null) {
            expect(array_key_exists($allowKey, $attributes))
                ->toBeTrue("CarrierSettings is missing allow attribute '{$allowKey}' for " . get_class($definition));
        }

        $priceKey = $definition->getPriceSettingsKey();
        if ($priceKey !== null) {
            expect(array_key_exists($priceKey, $attributes))
                ->toBeTrue("CarrierSettings is missing price attribute '{$priceKey}' for " . get_class($definition));
        }
    }
});

it('registers all definition-derived attributes on ProductSettings', function () {
    $definitions     = Pdk::get('orderOptionDefinitions');
    $productSettings = new ProductSettings();
    $attributes      = $productSettings->getAttributes();

    foreach ($definitions as $definition) {
        $productKey = $definition->getProductSettingsKey();
        if ($productKey !== null) {
            expect(array_key_exists($productKey, $attributes))
                ->toBeTrue("ProductSettings is missing attribute '{$productKey}' for " . get_class($definition));
        }
    }
});

it('registers all definition-derived attributes on ShipmentOptions', function () {
    $definitions     = Pdk::get('orderOptionDefinitions');
    $shipmentOptions = new ShipmentOptions();

    foreach ($definitions as $definition) {
        $key = $definition->getShipmentOptionsKey();
        if ($key !== null) {
            expect($shipmentOptions->getAttribute($key))->toBe(
                $definition->getShipmentOptionsDefault(),
                "ShipmentOptions attribute '{$key}' has wrong default for " . get_class($definition)
            );
        }
    }
});

it('registers all definition-derived attributes on Fulfilment ShipmentOptions', function () {
    $definitions        = Pdk::get('orderOptionDefinitions');
    $fulfilmentOptions  = new FulfilmentShipmentOptions();

    foreach ($definitions as $definition) {
        $key = $definition->getShipmentOptionsKey();
        if ($key !== null) {
            expect($fulfilmentOptions->getAttribute($key))->toBeNull(
                "Fulfilment ShipmentOptions attribute '{$key}' should default to null for " . get_class($definition)
            );
        }
    }
});

it('flows product settings through calculateShipmentOptions for all definitions with both product and shipment option keys', function () {
    $definitions = Pdk::get('orderOptionDefinitions');

    factory(Carrier::class)
        ->withAllCapabilities()
        ->store();

    foreach ($definitions as $definition) {
        $productKey  = $definition->getProductSettingsKey();
        $shipmentKey = $definition->getShipmentOptionsKey();

        if ($productKey === null || $shipmentKey === null) {
            continue;
        }

        $order = factory(PdkOrder::class)
            ->withDeliveryOptions(
                factory(DeliveryOptions::class)->withCarrier('POSTNL')
            )
            ->withLines(
                factory(PdkOrderLineCollection::class)->push(
                    factory(PdkOrderLine::class)->withProduct(
                        factory(\MyParcelNL\Pdk\App\Order\Model\PdkProduct::class)
                            ->withSettings(factory(ProductSettings::class)->with([$productKey => TriStateService::ENABLED]))
                    )
                )
            )
            ->make();

        /** @var PdkOrderOptionsServiceInterface $service */
        $service = Pdk::get(PdkOrderOptionsServiceInterface::class);
        $flags   = PdkOrderOptionsServiceInterface::EXCLUDE_SHIPMENT_OPTIONS | PdkOrderOptionsServiceInterface::EXCLUDE_CARRIER_SETTINGS;

        $newOrder = $service->calculateShipmentOptions($order, $flags);

        expect($newOrder->deliveryOptions->shipmentOptions->getAttribute($shipmentKey))->toBe(
            TriStateService::ENABLED,
            "Definition " . get_class($definition) . " with productKey='{$productKey}' should flow through to shipmentOptions['{$shipmentKey}'] as ENABLED"
        );
    }
});
