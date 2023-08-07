<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkProductRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use const MyParcelNL\Pdk\Tests\Datasets\KEY_DEFAULT;
use const MyParcelNL\Pdk\Tests\Datasets\KEY_DELIVERY_OPTIONS;
use const MyParcelNL\Pdk\Tests\Datasets\KEY_PRODUCT;

usesShared(new UsesMockPdkInstance());

afterEach(function () {
    /** @var MockPdkProductRepository $productRepository */
    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);
    $productRepository->reset();
});

dataset('carriers', [
    Carrier::CARRIER_BPOST_NAME              => Carrier::CARRIER_BPOST_NAME,
    Carrier::CARRIER_DHL_EUROPLUS_NAME       => Carrier::CARRIER_DHL_EUROPLUS_NAME,
    Carrier::CARRIER_DHL_FOR_YOU_NAME        => Carrier::CARRIER_DHL_FOR_YOU_NAME,
    Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME => Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
    Carrier::CARRIER_DPD_NAME                => Carrier::CARRIER_DPD_NAME,
    Carrier::CARRIER_POSTNL_NAME             => Carrier::CARRIER_POSTNL_NAME,
]);

function setupPdk(array $settings = []): PdkOrder
{
    /** @var SettingsRepositoryInterface $settingsRepository */
    $settingsRepository = Pdk::get(SettingsRepositoryInterface::class);
    /** @var MockPdkProductRepository $productRepository */
    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);

    $settingsRepository->storeAllSettings(
        new Settings([CarrierSettings::ID => new SettingsModelCollection($settings['carrierSettings'] ?? [])])
    );

    $productRepository->add(new PdkProductCollection($settings['products'] ?? []));

    return new PdkOrder(
        array_replace_recursive([
            'deliveryOptions' => [
                'carrier' => Carrier::CARRIER_POSTNL_NAME,
            ],
            'lines'           => array_map(
                static function (string $id) use ($productRepository): array {
                    return [
                        'quantity' => 1,
                        'product'  => $productRepository->getProduct($id),
                    ];
                },
                Arr::pluck($settings['products'] ?? [], 'externalIdentifier')
            ),
        ], $settings['order'] ?? [])
    );
}

it('calculates shipment options for child products', function ($key, $output, $options) {
    $order = setupPdk([
        'products' => [
            [
                'externalIdentifier' => 'PDK-I',
                'settings'           => [$key => $options[0] ?? -1],
                'parent'             => [
                    'externalIdentifier' => 'PDK-II',
                    'settings'           => [$key => $options[1] ?? -1],
                    'parent'             => [
                        'externalIdentifier' => 'PDK-III',
                        'settings'           => [$key => $options[2] ?? -1],
                    ],
                ],
            ],
        ],
    ]);

    $result = $order->lines[0]->settings->getAttribute($key);

    expect($result)->toBe($output);
})->with([
    '0, 1 -> 1'      => [
        'key'     => ProductSettings::EXPORT_SIGNATURE,
        'output'  => 1,
        'options' => [0, 1],
    ],
    '0, 0, 1 -> 1'   => [
        'key'     => ProductSettings::EXPORT_SIGNATURE,
        'output'  => 1,
        'options' => [0, 0, 1],
    ],
    '0, 0 -> 0'      => [
        'key'     => ProductSettings::EXPORT_SIGNATURE,
        'output'  => 0,
        'options' => [0, 0],
    ],
    '-1, -1, 0 -> 0' => [
        'key'     => ProductSettings::EXPORT_SIGNATURE,
        'output'  => 0,
        'options' => [-1, -1, 0],
    ],
    '-1, 1 -> 1'     => [
        'key'     => ProductSettings::EXPORT_SIGNATURE,
        'output'  => 1,
        'options' => [-1, 1],
    ],
    '-1, -1 -> -1'   => [
        'key'     => ProductSettings::EXPORT_SIGNATURE,
        'output'  => -1,
        'options' => [-1, -1],
    ],
]);

it('calculates shipment options from defaults and product settings', function (
    array $option,
    array $input,
    bool  $output
) {
    $order = setupPdk([
        'products'        => [
            [
                'externalIdentifier' => 'PDK-1',
            ],
            [
                'externalIdentifier' => 'PDK-2',
                'settings'           => [$option[KEY_PRODUCT] => $input[KEY_PRODUCT] ?? -1],
            ],
        ],
        'carrierSettings' => [
            Carrier::CARRIER_POSTNL_NAME => [$option[KEY_DEFAULT] => $input[KEY_DEFAULT] ?? 0],
        ],
    ]);

    /** @var ShipmentOptionsServiceInterface $service */
    $service  = Pdk::get(ShipmentOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->getAttribute($option[KEY_DELIVERY_OPTIONS]))->toBe($output);
})
    ->with('all shipment options')
    ->with([
        'default 0, product -1 -> false' => [
            'input'  => [
                KEY_DEFAULT => 0,
                KEY_PRODUCT => -1,
            ],
            'output' => false,
        ],
        'default 1, product -1 -> true'  => [
            'input'  => [
                KEY_DEFAULT => 1,
                KEY_PRODUCT => -1,
            ],
            'output' => true,
        ],
        'default 0, product 1 -> true'   => [
            'input'  => [
                KEY_DEFAULT => 0,
                KEY_PRODUCT => 1,
            ],
            'output' => true,
        ],
        'default 1, product 0 -> false'  => [
            'input'  => [
                KEY_DEFAULT => 1,
                KEY_PRODUCT => 0,
            ],
            'output' => false,
        ],
        'default 0, product 0 -> false'  => [
            'input'  => [
                KEY_DEFAULT => 0,
                KEY_PRODUCT => 0,
            ],
            'output' => false,
        ],
        'default 1, product 1 -> true'   => [
            'input'  => [
                KEY_DEFAULT => 1,
                KEY_PRODUCT => 1,
            ],
            'output' => true,
        ],
    ]);

it('calculates shipment options with delivery options', function (
    array $option,
    array $input,
    bool  $output
) {
    $order = setupPdk([
        'products'        => [
            [
                'externalIdentifier' => 'PDK-A',
            ],
            [
                'externalIdentifier' => 'PDK-B',
                'settings'           => [$option[KEY_PRODUCT] => $input[KEY_PRODUCT] ?? -1],
            ],
        ],
        'carrierSettings' => [
            Carrier::CARRIER_POSTNL_NAME => [$option[KEY_DEFAULT] => $input[KEY_DEFAULT] ?? 0],
        ],
        'order'           => [
            'deliveryOptions' => [
                'shipmentOptions' => [
                    $option[KEY_DELIVERY_OPTIONS] => $input[KEY_DELIVERY_OPTIONS] ?? 0,
                ],
            ],
        ],
    ]);

    /** @var ShipmentOptionsServiceInterface $service */
    $service  = Pdk::get(ShipmentOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    $result = $newOrder->deliveryOptions->shipmentOptions->getAttribute($option[KEY_DELIVERY_OPTIONS]);

    expect($result)->toBe($output);
})
    ->with('frontend shipment options')
    ->with([
        'default 0, product 0, checkout 1 -> true'   => [
            'input'  => [
                KEY_DEFAULT          => 0,
                KEY_PRODUCT          => 0,
                KEY_DELIVERY_OPTIONS => 1,
            ],
            'output' => true,
        ],
        'default 0, product 1, checkout 0 -> false'  => [
            'input'  => [
                KEY_DEFAULT          => 0,
                KEY_PRODUCT          => 1,
                KEY_DELIVERY_OPTIONS => 0,
            ],
            'output' => false,
        ],
        'default 1, product 0, checkout 0 -> false'  => [
            'input'  => [
                KEY_DEFAULT          => 1,
                KEY_PRODUCT          => 0,
                KEY_DELIVERY_OPTIONS => 0,
            ],
            'output' => false,
        ],
        'default 1, product 1, checkout 0 -> false'  => [
            'input'  => [
                KEY_DEFAULT          => 1,
                KEY_PRODUCT          => 1,
                KEY_DELIVERY_OPTIONS => 0,
            ],
            'output' => false,
        ],
        'default 1, product 1, checkout 1 -> true'   => [
            'input'  => [
                KEY_DEFAULT          => 1,
                KEY_PRODUCT          => 1,
                KEY_DELIVERY_OPTIONS => 1,
            ],
            'output' => true,
        ],
        'default 1, product 0, checkout 1 -> true'   => [
            'input'  => [
                KEY_DEFAULT          => 1,
                KEY_PRODUCT          => 0,
                KEY_DELIVERY_OPTIONS => 1,
            ],
            'output' => true,
        ],
        'default 0, product 1, checkout 1 -> true'   => [
            'input'  => [
                KEY_DEFAULT          => 0,
                KEY_PRODUCT          => 1,
                KEY_DELIVERY_OPTIONS => 1,
            ],
            'output' => true,
        ],
        'default 0, product 0, checkout 0 -> false'  => [
            'input'  => [
                KEY_DEFAULT          => 0,
                KEY_PRODUCT          => 0,
                KEY_DELIVERY_OPTIONS => 0,
            ],
            'output' => false,
        ],
        'default 0, product -1, checkout 1 -> true'  => [
            'input'  => [
                KEY_DEFAULT          => 0,
                KEY_PRODUCT          => -1,
                KEY_DELIVERY_OPTIONS => 1,
            ],
            'output' => true,
        ],
        'default 0, product -1, checkout 0 -> false' => [
            'input'  => [
                KEY_DEFAULT          => 0,
                KEY_PRODUCT          => -1,
                KEY_DELIVERY_OPTIONS => 0,
            ],
            'output' => false,
        ],
        'default 1, product -1, checkout 0 -> false' => [
            'input'  => [
                KEY_DEFAULT          => 1,
                KEY_PRODUCT          => -1,
                KEY_DELIVERY_OPTIONS => 0,
            ],
            'output' => false,
        ],
        'default 1, product -1, checkout 1 -> true'  => [
            'input'  => [
                KEY_DEFAULT          => 1,
                KEY_PRODUCT          => -1,
                KEY_DELIVERY_OPTIONS => 1,
            ],
            'output' => true,
        ],
    ]);

