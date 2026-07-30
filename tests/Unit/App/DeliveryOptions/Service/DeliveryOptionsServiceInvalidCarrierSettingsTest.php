<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use Mockery;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsManagerInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('checkout');

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('does not expose carriers when carrier settings are missing or invalid', function ($carrierSettings) {
    $settingsManager = Mockery::mock(SettingsManagerInterface::class);
    $settingsManager
        ->shouldReceive('get')
        ->andReturnUsing(static function (
            string $key,
            ?string $namespace = null,
            $default = null
        ) use ($carrierSettings) {
            return CarrierSettings::ID === $key && null === $namespace
                ? $carrierSettings
                : $default;
        });
    Pdk::set(SettingsManagerInterface::class, $settingsManager);

    /** @var DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $result = $service->createAllCarrierSettings(new PdkCart([
        'shippingMethod' => [
            'shippingAddress' => ['cc' => 'NL'],
        ],
        'lines' => [
            [
                'quantity' => 1,
                'product'  => [
                    'weight'        => 1000,
                    'isDeliverable' => true,
                ],
            ],
        ],
    ]));

    expect($result['packageType'])->toBe(DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME)
        ->and($result['carrierSettings'])->toBe([]);
})->with([
    'missing'       => [null],
    'empty array'   => [[]],
    'boolean value' => [false],
    'string value'  => ['invalid'],
]);
