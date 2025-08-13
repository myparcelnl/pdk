<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Calculator\General\InsuranceCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkProductRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

afterEach(function () {
    /** @var MockPdkProductRepository $productRepository */
    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);
    $productRepository->reset();
});

it('calculates insurance', function (array $input, int $result) {
    $reset   = mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);
    $carrier = $input['carrier'] ?? Carrier::CARRIER_POSTNL_NAME;

    factory(Settings::class)
        ->withCarrier(
            $carrier,
            array_replace([
                CarrierSettings::EXPORT_INSURANCE                  => true,
                CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT      => 0,
                CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE => 100,
                CarrierSettings::EXPORT_INSURANCE_UP_TO            => 500000,
                CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE     => 500000,
                CarrierSettings::EXPORT_INSURANCE_UP_TO_EU         => 500000,
                CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW        => 500000,
            ], $input['settings'] ?? [])
        )
        ->store();

    factory(PdkProduct::class)
        ->withExternalIdentifier('insurance')
        ->withPrice($input['orderPrice'] ?? 0)
        ->store();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc($input['country'] ?? 'NL'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->with($input['deliveryOptions'] ?? [])
                ->withCarrier($carrier)
        )
        ->withLines([
            factory(PdkOrderLine::class)
                ->withProduct('insurance')
                ->withPrice($input['orderPrice'] ?? 0),
        ])
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe($result);

    $reset();
})
    ->with([
        'amount passed in manually via delivery options' => [
            [
                'deliveryOptions' => [
                    'shipmentOptions' => [
                        'insurance' => 12345,
                    ],
                ],
            ],
            'result' => 25000,
        ],

        'value € 44.81 -> rounded up to € 100' => [
            [
                'orderPrice' => 4481,
            ],
            'result' => 10000,
        ],

        'value € 0 -> € 0' => [
            [
                'orderPrice' => 0,
            ],
            'result' => 0,
        ],

        'value € -100 -> € 0' => [
            [
                'orderPrice' => -100,
            ],
            'result' => 0,
        ],

        'value € 1 -> rounded up to € 100' => [
            [
                'orderPrice' => 1,
            ],
            'result' => 10000,
        ],

        'value € 150.50 -> rounded up to € 250' => [
            [
                'orderPrice' => 15050,
            ],
            'result' => 25000,
        ],

        'value € 1000 -> matches 100000' => [
            [
                'orderPrice' => 100000,
            ],
            'result' => 100000,
        ],

        'value € 1000.01 -> rounded up to € 1500' => [
            [
                'orderPrice' => 100001,
            ],
            'result' => 150000,
        ],

        'value € 1005 -> rounded up to € 1500' => [
            [
                'orderPrice' => 100500,
            ],
            'result' => 150000,
        ],

        'value € 10000 -> rounded up to € 5000' => [
            [
                'orderPrice' => 1000000,
            ],
            'result' => 500000,
        ],

        'value € 10000.01 -> rounded up to € 5000' => [
            [
                'orderPrice' => 1000001,
            ],
            'result' => 500000,
        ],

        'value € 10005 -> rounded up to € 5000' => [
            [
                'orderPrice' => 1000500,
            ],
            'result' => 500000,
        ],

        'value € 50, insured from € 100 -> € 0' => [
            [
                'orderPrice' => 5000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT => 100],
            ],
            'result' => 0,
        ],

        'value € 100, insured from € 100 -> matches € 100' => [
            [
                'orderPrice' => 10000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT => 100],
            ],
            'result' => 10000,
        ],

        'value € 3100, insured up to € 3000 -> € 3000' => [
            [
                'orderPrice' => 310000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_UP_TO => 300000],
            ],
            'result' => 300000,
        ],

        'value € 5000, insured up to € 3000 -> € 3000' => [
            [
                'orderPrice' => 500000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_UP_TO => 300000],
            ],
            'result' => 300000,
        ],

        'value € 310, percentage 50 -> rounded up to € 250' => [
            [
                'orderPrice' => 31000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE => 50],
            ],
            'result' => 25000,
        ],

        'value € 550, percentage 90 -> rounded up to € 500' => [
            [
                'orderPrice' => 55000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE => 90],
            ],
            'result' => 50000,
        ],

        'value € 400, percentage 150 -> rounded up to € 1000' => [
            [
                'orderPrice' => 40000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE => 150],
            ],
            'result' => 100000,
        ],

        'percentage 0 -> € 0' => [
            [
                'orderPrice' => 1000,
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO            => 100,
                    CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE => 0,
                ],
            ],
            'result' => 0,
        ],

        'percentage -90 -> € 0' => [
            [
                'orderPrice' => 5000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE => -90],
            ],
            'result' => 0,
        ],

        'percentage -500 -> € 0' => [
            [
                'orderPrice' => 5000,
                'settings'   => [CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE => -500],
            ],
            'result' => 0,
        ],

        'country BE' => [
            [
                'orderPrice' => 5000,
                'country'    => 'BE',
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE => 0,
                ],
            ],
            'result' => 0,
        ],

        'country BE: with unique insurance' => [
            [
                'orderPrice' => 5000,
                'country'    => 'BE',
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE => 10000,
                ],
            ],
            'result' => 10000,
        ],

        'country DE' => [
            [
                'orderPrice' => 5000,
                'country'    => 'DE',
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_EU => 0,
                ],
            ],
            'result' => 0,
        ],

        'country DE: with EU insurance' => [
            [
                'orderPrice' => 5000,
                'country'    => 'DE',
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_EU => 20000,
                ],
            ],
            'result' => 5000,
        ],

        'country US' => [
            [
                'orderPrice' => 1,
                'country'    => 'US',
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW => 0,
                ],
            ],
            'result' => 0,
        ],

        'country US: with ROW insurance' => [
            [
                'orderPrice' => 1,
                'country'    => 'US',
                'settings'   => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW => 10000,
                ],
            ],
            'result' => 5000,
        ],

        sprintf('carrier %s', Carrier::CARRIER_DHL_FOR_YOU_NAME) => [
            [
                'orderPrice' => 160000,
                'carrier'    => Carrier::CARRIER_DHL_FOR_YOU_NAME,
            ],
            'result' => 200000,
        ],

        sprintf('carrier %s', Carrier::CARRIER_DHL_EUROPLUS_NAME) => [
            [
                'country'    => 'DE',
                'orderPrice' => 360000,
                'carrier'    => Carrier::CARRIER_DHL_EUROPLUS_NAME,
            ],
            'result' => 400000,
        ],

        sprintf('carrier %s', Carrier::CARRIER_GLS_NAME) => [
            [
                'orderPrice' => 10000,
                'carrier'    => Carrier::CARRIER_GLS_NAME,
            ],
            'result' => 10000,
        ],
    ]);

it('calculates insurance for fixed insurance amount when insurance is disabled', function () {
    mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);

    factory(Settings::class)
        ->withCarrier(
            Carrier::CARRIER_DPD_NAME,
            [
                CarrierSettings::EXPORT_INSURANCE => false,
            ]
        )
        ->store();

    $carrier = factory(Carrier::class)
        ->withName(Carrier::CARRIER_DPD_NAME)
        ->withOutboundFeatures(factory(PropositionCarrierFeatures::class)->withShipmentOptions(['insurance' => [52000]]))
        ->make();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions(factory(ShipmentOptions::class)->withInsurance(0))
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe(52000);
});
