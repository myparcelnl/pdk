<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Context\Model\CheckoutContext;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('checkout');

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

/**
 * A deliverable single-line cart with an NL shipping address — the minimum a capabilities
 * lookup needs (country + weight).
 */
function cartWithNlAddress(array $productSettings = []): PdkCart
{
    return new PdkCart([
        'shippingMethod' => [
            'shippingAddress' => ['cc' => 'NL'],
        ],
        'lines'          => [
            [
                'quantity' => 1,
                'product'  => [
                    'isDeliverable' => true,
                    'weight'        => 1,
                    'settings'      => $productSettings,
                ],
            ],
        ],
    ]);
}

function storeShopWithCarrier(string $carrierName, callable $settingsCallback = null): void
{
    $carrierFactory = factory(Carrier::class)
        ->withCarrier($carrierName)
        ->withAllCapabilities();

    $settingsFactory = factory(CarrierSettings::class, $carrierName)->withDeliveryOptions();

    if ($settingsCallback) {
        $settingsFactory = $settingsCallback($settingsFactory);
    }

    $settingsFactory->store();

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($carrierFactory))
        ->store();
}

it('calculates shipment options per carrier including capability requires', function () {
    storeShopWithCarrier('POSTNL', function (object $settings) {
        return $settings->withExportAgeCheck(true);
    });

    /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $result = $service->createCartShipmentOptions(cartWithNlAddress());

    /** @var ShipmentOptions $options */
    $options = $result->get('postnl');

    expect($options)->toBeInstanceOf(ShipmentOptions::class)
        ->and($options->ageCheck)->toBe(TriStateService::ENABLED)
        ->and($options->signature)->toBe(TriStateService::ENABLED)
        ->and($options->onlyRecipient)->toBe(TriStateService::ENABLED)
        ->and($options->receiptCode)->toBe(TriStateService::DISABLED);
});

it('includes options activated through product settings', function () {
    storeShopWithCarrier('POSTNL');

    /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $result = $service->createCartShipmentOptions(
        cartWithNlAddress(['exportAgeCheck' => TriStateService::ENABLED])
    );

    /** @var ShipmentOptions $options */
    $options = $result->get('postnl');

    expect($options->ageCheck)->toBe(TriStateService::ENABLED)
        ->and($options->signature)->toBe(TriStateService::ENABLED)
        ->and($options->onlyRecipient)->toBe(TriStateService::ENABLED);
});

it('exposes the options as widget-format booleans through the checkout context', function () {
    storeShopWithCarrier('POSTNL', function (object $settings) {
        return $settings->withExportAgeCheck(true);
    });

    $context = CheckoutContext::fromCart(cartWithNlAddress());

    expect($context->cartShipmentOptions)->toHaveKey('postnl')
        ->and($context->cartShipmentOptions['postnl']['ageCheck'])->toBe(true)
        ->and($context->cartShipmentOptions['postnl']['signature'])->toBe(true)
        ->and($context->cartShipmentOptions['postnl']['onlyRecipient'])->toBe(true)
        ->and($context->cartShipmentOptions['postnl']['receiptCode'])->toBe(false)
        ->and($context->toArray())->toHaveKey('cartShipmentOptions');
});

it('always contains an entry per carrier with definitive values', function () {
    storeShopWithCarrier('POSTNL');

    /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $result = $service->createCartShipmentOptions(cartWithNlAddress());

    /** @var ShipmentOptions $options */
    $options = $result->get('postnl');

    // The calculation pipeline always resolves options to definitive values (that is what an
    // export needs), so inactive options come out as explicit DISABLED — never as INHERIT.
    expect($options)->toBeInstanceOf(ShipmentOptions::class)
        ->and($options->ageCheck)->toBe(TriStateService::DISABLED)
        ->and($options->signature)->toBe(TriStateService::DISABLED);
});
