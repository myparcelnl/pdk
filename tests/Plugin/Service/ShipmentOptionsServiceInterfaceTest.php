<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Plugin\Service;

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;
use MyParcelNL\Pdk\Plugin\Service\ShipmentOptionsServiceInterface;
use MyParcelNL\Pdk\Product\Repository\ProductRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Settings\Repository\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Bootstrap\MockProductRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use function DI\autowire;

const CARRIER = 'postnl';

const DEFAULT_SHIPMENT_OPTIONS = [
    ShipmentOptions::AGE_CHECK         => false,
    ShipmentOptions::INSURANCE         => 0,
    ShipmentOptions::LABEL_DESCRIPTION => '',
    ShipmentOptions::LARGE_FORMAT      => false,
    ShipmentOptions::ONLY_RECIPIENT    => false,
    ShipmentOptions::RETURN            => false,
    ShipmentOptions::SAME_DAY_DELIVERY => false,
    ShipmentOptions::SIGNATURE         => false,
];

const KEY_ENABLED_VALUE   = 'enabledValue';
const KEY_DISABLED_VALUE  = 'disabledValue';
const KEY_CARRIER_SETTING = 'carrierSetting';
const KEY_PRODUCT_SETTING = 'productSetting';
const KEY_SHIPMENT_OPTION = 'shipmentOption';

dataset('shipment options', [
    'age check'      => [
        [
            KEY_ENABLED_VALUE   => true,
            KEY_DISABLED_VALUE  => false,
            KEY_CARRIER_SETTING => CarrierSettings::EXPORT_AGE_CHECK,
            KEY_PRODUCT_SETTING => ProductSettings::EXPORT_AGE_CHECK,
            KEY_SHIPMENT_OPTION => ShipmentOptions::AGE_CHECK,
        ],
    ],
    'insurance'      => [
        [
            KEY_ENABLED_VALUE   => 1,
            KEY_DISABLED_VALUE  => 0,
            KEY_CARRIER_SETTING => CarrierSettings::EXPORT_INSURANCE,
            KEY_PRODUCT_SETTING => ProductSettings::EXPORT_INSURANCE,
            KEY_SHIPMENT_OPTION => ShipmentOptions::INSURANCE,
        ],
    ],
    'large format'   => [
        [
            KEY_ENABLED_VALUE   => true,
            KEY_DISABLED_VALUE  => false,
            KEY_CARRIER_SETTING => CarrierSettings::EXPORT_LARGE_FORMAT,
            KEY_PRODUCT_SETTING => ProductSettings::EXPORT_LARGE_FORMAT,
            KEY_SHIPMENT_OPTION => ShipmentOptions::LARGE_FORMAT,
        ],
    ],
    'only recipient' => [
        [
            KEY_ENABLED_VALUE   => true,
            KEY_DISABLED_VALUE  => false,
            KEY_CARRIER_SETTING => CarrierSettings::EXPORT_ONLY_RECIPIENT,
            KEY_PRODUCT_SETTING => ProductSettings::EXPORT_ONLY_RECIPIENT,
            KEY_SHIPMENT_OPTION => ShipmentOptions::ONLY_RECIPIENT,
        ],
    ],
    'return'         => [
        [
            KEY_ENABLED_VALUE   => true,
            KEY_DISABLED_VALUE  => false,
            KEY_SHIPMENT_OPTION => ShipmentOptions::RETURN,
            KEY_CARRIER_SETTING => CarrierSettings::EXPORT_RETURN,
            KEY_PRODUCT_SETTING => ProductSettings::EXPORT_RETURN,
        ],
    ],
    'signature'      => [
        [
            KEY_ENABLED_VALUE   => true,
            KEY_DISABLED_VALUE  => false,
            KEY_CARRIER_SETTING => CarrierSettings::EXPORT_SIGNATURE,
            KEY_PRODUCT_SETTING => ProductSettings::EXPORT_SIGNATURE,
            KEY_SHIPMENT_OPTION => ShipmentOptions::SIGNATURE,
        ],
    ],
]);

function mockPdk(array $carrierSettings = [], array $products = []): void
{
    PdkFactory::create(
        MockPdkConfig::create([
            ProductRepositoryInterface::class  => autowire(MockProductRepository::class)->constructor($products),
            SettingsRepositoryInterface::class => autowire(MockSettingsRepository::class)->constructor(
                [CarrierSettings::ID => [CARRIER => $carrierSettings]]
            ),
        ])
    );
}

function expectShipmentOptionToEqual(PdkOrder $order, array $options): void
{
    expect($order->deliveryOptions->shipmentOptions->toArray())->toEqual(
        array_merge(DEFAULT_SHIPMENT_OPTIONS, $options)
    );
}

it('inherits shipment option from carrier settings', function (array $option, string $key) {
    mockPdk([$option[KEY_CARRIER_SETTING] => $option[$key]]);

    $order = new PdkOrder(['deliveryOptions' => ['carrier' => CARRIER]]);

    /** @var \MyParcelNL\Pdk\Plugin\Service\ShipmentOptionsServiceInterface $service */
    $service = Pdk::get(ShipmentOptionsServiceInterface::class);
    $service->calculate($order);

    expectShipmentOptionToEqual($order, [$option[KEY_SHIPMENT_OPTION] => $option[$key]]);
}
)
    ->with('shipment options')
    ->with(['enabled' => KEY_ENABLED_VALUE, 'disabled' => KEY_DISABLED_VALUE]);

it('prioritizes product settings of any order line over defaults', function (array $option) {
    mockPdk(
        [$option[KEY_CARRIER_SETTING] => $option[KEY_ENABLED_VALUE]],
        [
            ['externalIdentifier' => 'PDK-1'],
            [
                'externalIdentifier' => 'PDK-2',
                'settings'           => [$option[KEY_PRODUCT_SETTING] => $option[KEY_DISABLED_VALUE]],
            ],
        ]
    );

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockProductRepository $productRepository */
    $productRepository = Pdk::get(ProductRepositoryInterface::class);

    $order = new PdkOrder([
        'deliveryOptions' => ['carrier' => CARRIER],
        'lines'           => [
            ['product' => $productRepository->getProduct('PDK-1')],
            ['product' => $productRepository->getProduct('PDK-2')],
        ],
    ]);

    /** @var \MyParcelNL\Pdk\Plugin\Service\ShipmentOptionsServiceInterface $service */
    $service = Pdk::get(ShipmentOptionsServiceInterface::class);
    $service->calculate($order);

    expectShipmentOptionToEqual($order, [$option[KEY_SHIPMENT_OPTION] => $option[KEY_DISABLED_VALUE]]);
}
)->with('shipment options');

it('prioritizes override over product settings and defaults', function (array $option) {
    mockPdk(
        [$option[KEY_CARRIER_SETTING] => $option[KEY_ENABLED_VALUE]],
        [
            [
                'externalIdentifier' => 'PDK-1',
                'settings'           => [$option[KEY_PRODUCT_SETTING] => $option[KEY_DISABLED_VALUE]],
            ],
        ]
    );

    /** @var \MyParcelNL\Pdk\Product\Repository\ProductRepositoryInterface $productRepository */
    $productRepository = Pdk::get(ProductRepositoryInterface::class);

    $order = new PdkOrder([
        'deliveryOptions' => [
            'carrier'         => CARRIER,
            'shipmentOptions' => [$option[KEY_SHIPMENT_OPTION] => $option[KEY_DISABLED_VALUE]],
        ],
        'lines'           => [['product' => $productRepository->getProduct('PDK-1')]],
    ]);

    /** @var \MyParcelNL\Pdk\Plugin\Service\ShipmentOptionsServiceInterface $service */
    $service = Pdk::get(ShipmentOptionsServiceInterface::class);
    $service->calculate($order);

    expectShipmentOptionToEqual($order, [$option[KEY_SHIPMENT_OPTION] => $option[KEY_DISABLED_VALUE]]);
})->with('shipment options');

it('calculates insurance', function (int $insurance, int $result) {
    mockPdk([], [
        [
            'externalIdentifier' => 'PDK-1',
            'settings'           => [ProductSettings::EXPORT_INSURANCE => 1],
        ],
    ]);

    /** @var \MyParcelNL\Pdk\Product\Repository\ProductRepositoryInterface $productRepository */
    $productRepository = Pdk::get(ProductRepositoryInterface::class);

    $order = new PdkOrder([
        'deliveryOptions' => [
            'carrier'         => CARRIER,
            'shipmentOptions' => ['insurance' => $insurance],
        ],
        'lines'           => [['product' => $productRepository->getProduct('PDK-1')]],
    ]);

    /** @var \MyParcelNL\Pdk\Plugin\Service\ShipmentOptionsServiceInterface $service */
    $service = Pdk::get(ShipmentOptionsServiceInterface::class);
    $service->calculate($order);

    expect($order->deliveryOptions->shipmentOptions->insurance)->toBe($result);
})->with([
    [4000, 4000],
    [4001, 4001],
    [5050, 5050],
    [100, 100],
    [0, 0],
    [-1, 0],
    [-100, 0],
    [100000, 100000],
    [100001, 100000],
    [100500, 100500],
    [1000000, 100000],
    [1000001, 100000],
    [1000500, 100000],
]);

it('merges product settings', function (array $input, array $results) {
    mockPdk([],
        // For each value in $results, create a product with that value in its settings.
        array_map(function (int $value, int $index) use ($input) {
            return [
                'externalIdentifier' => "PDK-$index",
                'settings'           => [$input[KEY_PRODUCT_SETTING] => $value],
            ];
        }, $results, array_keys($results)));

    /** @var \MyParcelNL\Pdk\Product\Repository\ProductRepositoryInterface $productRepository */
    $productRepository = Pdk::get(ProductRepositoryInterface::class);

    // Create a list of order lines containing one of each product.
    $lines = array_map(function (PdkProduct $product) {
        return ['product' => $product, 'quantity' => 1];
    },
        $productRepository->getProducts()
            ->all());

    $order = new PdkOrder(['deliveryOptions' => ['carrier' => CARRIER], 'lines' => $lines]);

    /** @var \MyParcelNL\Pdk\Plugin\Service\ShipmentOptionsServiceInterface $service */
    $service = Pdk::get(ShipmentOptionsServiceInterface::class);
    $service->calculate($order);

    expectShipmentOptionToEqual($order, [$input[KEY_SHIPMENT_OPTION] => $input[$results['output']]]);
})
    ->with('shipment options')
    ->with([
        '-1, 0, 1 -> "enabled"' => [
            'input'  => [-1, 0, 1],
            'output' => KEY_ENABLED_VALUE,
        ],
        '0, -1 -> disabled'     => [
            'input'  => [0, -1],
            'output' => KEY_DISABLED_VALUE,
        ],
        '1, 0 -> enabled'       => [
            'input'  => [1, 0],
            'output' => KEY_ENABLED_VALUE,
        ],
        '-1 -> disabled'        => [
            'input'  => [-1],
            'output' => KEY_DISABLED_VALUE,
        ],
    ]);