<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkProductRepository;
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

function mockPdk(array $carrierSettings = [], array $products = [], array $checkoutSettings = []): void
{
    MockPdkFactory::create([
        PdkProductRepositoryInterface::class => autowire(MockPdkProductRepository::class)->constructor($products),
        SettingsRepositoryInterface::class   => autowire(MockSettingsRepository::class)->constructor(
            [
                CarrierSettings::ID  => [CARRIER => $carrierSettings],
                CheckoutSettings::ID => $checkoutSettings,
            ]
        ),
    ]);
}

function expectShipmentOptionToEqual(PdkOrder $order, array $options): void
{
    expect($order->deliveryOptions->shipmentOptions)
        ->toArray()
        ->toHaveKeysAndValues($options);
}

it('inherits shipment option from carrier settings', function (array $option, string $key) {
    mockPdk([$option[KEY_CARRIER_SETTING] => $option[$key]]);

    $order = new PdkOrder(['deliveryOptions' => ['carrier' => CARRIER]]);

    /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface $service */
    $service = Pdk::get(ShipmentOptionsServiceInterface::class);
    $service->calculate($order);

    expectShipmentOptionToEqual($order, [$option[KEY_SHIPMENT_OPTION] => $option[$key]]);
})
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

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkProductRepository $productRepository */
    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);

    $order = new PdkOrder([
        'deliveryOptions' => ['carrier' => CARRIER],
        'lines'           => [
            ['product' => $productRepository->getProduct('PDK-1')],
            ['product' => $productRepository->getProduct('PDK-2')],
        ],
    ]);

    /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface $service */
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

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface $productRepository */
    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);

    $order = new PdkOrder([
        'deliveryOptions' => [
            'carrier'         => CARRIER,
            'shipmentOptions' => [$option[KEY_SHIPMENT_OPTION] => $option[KEY_DISABLED_VALUE]],
        ],
        'lines'           => [['product' => $productRepository->getProduct('PDK-1')]],
    ]);

    /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface $service */
    $service = Pdk::get(ShipmentOptionsServiceInterface::class);
    $service->calculate($order);

    expectShipmentOptionToEqual($order, [$option[KEY_SHIPMENT_OPTION] => $option[KEY_DISABLED_VALUE]]);
})->with('shipment options');

it(
    'calculates insurance',
    function (int $insuranceFrom, int $insuranceUpTo, int $orderTotal, float $factor, int $result) {
        mockPdk([
            CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT  => $insuranceFrom,
            CarrierSettings::EXPORT_INSURANCE_UP_TO        => $insuranceUpTo,
            CarrierSettings::EXPORT_INSURANCE_PRICE_FACTOR => $factor,
        ], [
            [
                'externalIdentifier' => 'PDK-1',
                'price'              => ['currency' => 'EUR', 'amount' => $orderTotal],
            ],
        ]);

        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface $productRepository */
        $productRepository = Pdk::get(PdkProductRepositoryInterface::class);

        $order = new PdkOrder([
            'deliveryOptions' => [
                'carrier'         => CARRIER,
                'shipmentOptions' => ['insurance' => 1],
            ],
            'lines'           => [['price' => $orderTotal, 'product' => $productRepository->getProduct('PDK-1')]],
            'recipient'       => ['cc' => 'NL'],
        ]);

        /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface $service */
        $service = Pdk::get(ShipmentOptionsServiceInterface::class);
        $service->calculate($order);

        expect($order->deliveryOptions->shipmentOptions->insurance)->toBe($result);
    }
)->with([
    [0, 5000, 4481, 1, 10000],
    [0, 5000, 0, 1, 0],
    [0, 5000, -1, 1, 0],
    [0, 5000, -100, 1, 0],
    [0, 5000, 1, 1, 10000],
    [0, 5000, 15050, 1, 25000],
    [0, 5000, 100000, 1, 100000],
    [0, 5000, 100001, 1, 150000],
    [0, 5000, 100500, 1, 150000],
    [0, 5000, 1000000, 1, 500000],
    [0, 5000, 1000001, 1, 500000],
    [0, 5000, 1000500, 1, 500000],
    [100, 5000, 5000, 1, 0],
    [0, 3000, 310000, 1, 300000],
    [0, 3000, 5000, 1, 10000],
    [0, 5000, 31000, .5, 25000],
    [0, 5000, 55000, .9, 50000],
    [0, 5000, 40000, 1.5, 100000],
    [0, 100, 1000, 0, 0],
    [0, 5000, 5000, -0.9, 0],
    [0, 5000, 5000, -5, 0],
]);

it('merges product settings', function (array $input, array $results, $output) {
    mockPdk([],
        // For each value in $results, create a product with that value in its settings.
        array_map(function (int $value, int $index) use ($input) {
            return [
                'externalIdentifier' => "PDK-$index",
                'settings'           => [$input[KEY_PRODUCT_SETTING] => $value],
            ];
        }, $results, array_keys($results)));

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface $productRepository */
    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);

    // Create a list of order lines containing one of each product.
    $lines = array_map(function (PdkProduct $product) {
        return ['product' => $product, 'quantity' => 1];
    },
        $productRepository->getProducts()
            ->all());

    $order = new PdkOrder(['deliveryOptions' => ['carrier' => CARRIER], 'lines' => $lines]);

    /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface $service */
    $service = Pdk::get(ShipmentOptionsServiceInterface::class);
    $service->calculate($order);

    expectShipmentOptionToEqual($order, [$input[KEY_SHIPMENT_OPTION] => $input[$output]]);
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
