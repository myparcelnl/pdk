<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\App\Order\Repository\MockPdkProductRepository;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;

afterEach(function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\App\Order\Repository\MockPdkProductRepository $productRepository */
    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);
    $productRepository->reset();
});

it('calculates insurance', function (array $input) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\Settings\Repository\MockSettingsRepository $settingsRepository */
    $settingsRepository = Pdk::get(SettingsRepositoryInterface::class);
    /** @var MockPdkProductRepository $productRepository */
    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);

    $settingsRepository->storeAllSettings(
        new Settings([
            CarrierSettings::ID => [
                    $input['carrier'] ?? Carrier::CARRIER_POSTNL_NAME => array_replace([
                    CarrierSettings::EXPORT_INSURANCE              => true,
                    CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT  => 0,
                    CarrierSettings::EXPORT_INSURANCE_PRICE_FACTOR => 1,
                    CarrierSettings::EXPORT_INSURANCE_UP_TO        => 5000,
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE => 5000,
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_EU     => 5000,
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW    => 5000,
                ],
                    $input['settings'] ?? []),
            ],
        ])
    );

    $product = new PdkProduct([
        'externalIdentifier' => 'insurance',
        'price'              => ['amount' => $input['orderPrice'] ?? 0],
    ]);

    $productRepository->add(new PdkProductCollection([$product]));

    $order = new PdkOrder([
        'deliveryOptions' => $input['deliveryOptions'] ?? [],
        'lines'           => [
            [
                'quantity' => 1,
                'product'  => $product,
                'price'    => $input['orderPrice'] ?? 0,
            ],
        ],
        'shippingAddress' => ['cc' => $input['country'] ?? 'NL'],
    ]);

    /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface $service */
    $service  = Pdk::get(ShipmentOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe($input['result'] ?? 0);
})
    ->with([
        'amount passed in manually via delivery options' => [
            [
                'deliveryOptions' => [
                    'shipmentOptions' => [
                        'insurance' => 12345,
                    ],
                ],
                'result'          => 12345,
            ],
        ],

        'value € 44.81 -> rounded up to € 100' => [
            [
                'orderPrice' => 4481,
                'result'     => 10000,
            ],
        ],

        'value € 0 -> € 0' => [
            [
                'orderPrice' => 0,
                'result'     => 0,
            ],
        ],

        'value € -100 -> € 0' => [
            [
                'orderPrice' => -100,
                'result'     => 0,
            ],
        ],

        'value €. 1 -> rounded up to € 100' => [
            [
                'orderPrice' => 1,
                'result'     => 10000,
            ],
        ],

        'value € 150.50 -> rounded up to € 250' => [
            [
                'orderPrice' => 15050,
                'result'     => 25000,
            ],
        ],

        'value € 1000 -> matches 100000' => [
            [
                'orderPrice' => 100000,
                'result'     => 100000,
            ],
        ],

        'value € 1000.01 -> rounded up to € 1500' => [
            [
                'orderPrice' => 100001,
                'result'     => 150000,
            ],
        ],

        'value € 1005 -> rounded up to € 1500' => [
            [
                'orderPrice' => 100500,
                'result'     => 150000,
            ],
        ],

        'value € 10000 -> rounded up to € 5000' => [
            [
                'orderPrice' => 1000000,
                'result'     => 500000,
            ],
        ],

        'value € 10000.01 -> rounded up to € 5000' => [
            [
                'orderPrice' => 1000001,
                'result'     => 500000,
            ],
        ],

        'value € 10005 -> rounded up to € 5000' => [
            [
                'orderPrice' => 1000500,
                'result'     => 500000,
            ],
        ],

        'value € 50, insured from € 100 -> € 0' => [
            [
                'orderPrice' => 5000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT => 100],
                'result'     => 0,
            ],
        ],

        'value € 100, insured from € 100 -> matches € 100' => [
            [
                'orderPrice' => 10000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT => 100],
                'result'     => 10000,
            ],
        ],

        'value € 3100, insured up to € 3000 -> € 3000' => [
            [
                'orderPrice' => 310000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_UP_TO => 3000],
                'result'     => 300000,
            ],
        ],

        'value € 5000, insured up to € 3000 -> € 10000' => [
            [
                'orderPrice' => 5000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_UP_TO => 3000],
                'result'     => 10000,
            ],
        ],

        'value € 310, factor .5 -> rounded up to € 250' => [
            [
                'orderPrice' => 31000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_PRICE_FACTOR => .5],
                'result'     => 25000,
            ],
        ],

        'value € 550, factor .9 -> rounded up to € 500' => [
            [
                'orderPrice' => 55000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_PRICE_FACTOR => .9],
                'result'     => 50000,
            ],
        ],

        'value € 400, factor 1.5 -> rounded up to € 1000' => [
            [
                'orderPrice' => 40000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_PRICE_FACTOR => 1.5],
                'result'     => 100000,
            ],
        ],

        'factor 0 -> € 0' => [
            [
                'orderPrice' => 1000,
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO        => 100,
                    CarrierSettings::EXPORT_INSURANCE_PRICE_FACTOR => 0,
                ],
                'result'     => 0,
            ],
        ],

        'factor -0.9 -> € 0' => [
            [
                'orderPrice' => 5000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_PRICE_FACTOR => -0.9],
                'result'     => 0,
            ],
        ],

        'factor -5 -> € 0' => [
            [
                'orderPrice' => 5000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_PRICE_FACTOR => -5],
                'result'     => 0,
            ],
        ],

        'country BE' => [
            [
                'orderPrice' => 5000,
                'country'    => 'BE',
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE => 0,
                ],
                'result'     => 0,
            ],
        ],

        'country BE: with unique insurance' => [
            [
                'orderPrice' => 5000,
                'country'    => 'BE',
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE => 10000,
                ],
                'result'     => 10000,
            ],
        ],

        'country DE' => [
            [
                'orderPrice' => 5000,
                'country'    => 'DE',
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_EU => 0,
                ],
                'result'     => 0,
            ],
        ],

        'country DE: with EU insurance' => [
            [
                'orderPrice' => 5000,
                'country'    => 'DE',
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_EU => 20000,
                ],
                'result'     => 5000,
            ],
        ],

        'country US' => [
            [
                'orderPrice' => 1,
                'country'    => 'US',
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW => 0,
                ],
                'result'     => 0,
            ],
        ],

        'country US: with ROW insurance' => [
            [
                'orderPrice' => 1,
                'country'    => 'US',
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW => 10000,
                ],
                'result'     => 5000,
            ],
        ],

        sprintf('carrier %s', Carrier::CARRIER_DHL_FOR_YOU_NAME) => [
            [
                'orderPrice' => 5000,
                'carrier'    => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                'result'     => 10000,
            ],
        ],

        sprintf('carrier %s', Carrier::CARRIER_DHL_EUROPLUS_NAME) => [
            [
                'country'    => 'DE',
                'orderPrice' => 5000,
                'carrier'    => Carrier::CARRIER_DHL_EUROPLUS_NAME,
                'result'     => 5000,
            ],
        ],

        sprintf('carrier %s', Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME) => [
            [
                'country'    => 'FR',
                'orderPrice' => 5000,
                'carrier'    => Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
                'result'     => 5000,
            ],
        ],
    ]);
