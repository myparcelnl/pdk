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
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkProductRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

afterEach(function () {
    /** @var MockPdkProductRepository $productRepository */
    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);
    $productRepository->reset();
});

it('calculates insurance', function (array $input, int $result) {
    $reset   = mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);
    $carrier = $input['carrier'] ?? RefCapabilitiesSharedCarrierV2::POSTNL;

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

        'manual amount respects schema but ignores export settings limit' => [
            [
                'deliveryOptions' => [
                    'shipmentOptions' => [
                        'insurance' => 10000,
                    ],
                ],
                'settings' => [
                    CarrierSettings::EXPORT_INSURANCE_UP_TO => 0,
                ],
            ],
            'result' => 10000,
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

        'value € 50, insured from € 100 -> carrier minimum' => [
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

        sprintf('carrier %s', RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU) => [
            [
                'orderPrice' => 160000,
                'carrier'    => RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU,
            ],
            'result' => 200000,
        ],

        sprintf('carrier %s', RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS) => [
            [
                'country'    => 'DE',
                'orderPrice' => 360000,
                'carrier'    => RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS,
            ],
            'result' => 400000,
        ],

        sprintf('carrier %s', RefCapabilitiesSharedCarrierV2::GLS) => [
            [
                'orderPrice' => 10000,
                'carrier'    => RefCapabilitiesSharedCarrierV2::GLS,
            ],
            'result' => 10000,
        ],
    ]);

it('calculates insurance for fixed insurance amount when insurance is disabled', function () {
    mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);

    // DPD is configured with a fixed mandatory insurance tier of 52000 (min = default = max).
    // PostNL is included to satisfy the proposition default-carrier requirement.
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL))
                ->push(
                    factory(Carrier::class)
                        ->withCarrier(RefCapabilitiesSharedCarrierV2::DPD)
                        ->withInsurance(52000, 52000, 52000)
                )
        )
        ->store();

    factory(Settings::class)
        ->withCarrier(
            RefCapabilitiesSharedCarrierV2::DPD,
            [
                CarrierSettings::EXPORT_INSURANCE => false,
            ]
        )
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier(RefCapabilitiesSharedCarrierV2::DPD)
                ->withShipmentOptions(factory(ShipmentOptions::class)->withInsurance(0))
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe(52000);
});

it('returns capabilities default amount when no insurance is set on the order', function () {
    mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);

    // TRUNKRS has no insurance enum in its JSON schema, so the capabilities path is always taken.
    // default=50000 means: when no amount is specified, the carrier default is used.
    // Build a shop with only TRUNKRS so that we control exactly which insurance capabilities are returned.
    // PostNL is included to satisfy the proposition default-carrier requirement.
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL))
                ->push(
                    factory(Carrier::class)
                        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
                        ->withInsurance(50000, 0, 200000)
                )
        )
        ->store();

    factory(Settings::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS, [
            CarrierSettings::EXPORT_INSURANCE => false,
        ])
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
                ->withShipmentOptions(factory(ShipmentOptions::class)->withInsurance(-1))
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe(50000);
});

it('capabilities fallback: order price rounds up to the nearest tier', function () {
    mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);

    // TRUNKRS has no insurance enum in its JSON schema, so the capabilities range [0,50000,100000,150000,200000] is used.
    // Build a shop with only TRUNKRS so that we control exactly which insurance capabilities are returned.
    // PostNL is included to satisfy the proposition default-carrier requirement.
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL))
                ->push(
                    factory(Carrier::class)
                        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
                        ->withInsurance(0, 0, 200000)
                )
        )
        ->store();

    factory(Settings::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS, [
            CarrierSettings::EXPORT_INSURANCE                  => true,
            CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT      => 0,
            CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE => 100,
            CarrierSettings::EXPORT_INSURANCE_UP_TO            => 200000,
        ])
        ->store();

    // orderPrice 100001 → orderAmount 100001 → nearest tier ≥ 100001 in [0,50000,100000,150000,200000] = 150000
    factory(PdkProduct::class)->withExternalIdentifier('trunkrs-product')->withPrice(100001)->store();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS))
        ->withLines([factory(PdkOrderLine::class)->withProduct('trunkrs-product')->withPrice(100001)])
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe(150000);
});

it('capabilities fallback: order price exceeding capabilities max is capped at capabilities max', function () {
    mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);

    // EXPORT_INSURANCE_UP_TO is intentionally higher than the capabilities max (200000).
    // The result must be capped at the capabilities max, not the settings limit.
    // Build a shop with only TRUNKRS so that we control exactly which insurance capabilities are returned.
    // PostNL is included to satisfy the proposition default-carrier requirement.
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL))
                ->push(
                    factory(Carrier::class)
                        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
                        ->withInsurance(0, 0, 200000)
                )
        )
        ->store();

    factory(Settings::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS, [
            CarrierSettings::EXPORT_INSURANCE                  => true,
            CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT      => 0,
            CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE => 100,
            CarrierSettings::EXPORT_INSURANCE_UP_TO            => 500000,
        ])
        ->store();

    // orderPrice 1000000 (€10000) → exceeds all tiers → caps at end([0,50000,...,200000]) = 200000
    factory(PdkProduct::class)->withExternalIdentifier('trunkrs-product-large')->withPrice(1000000)->store();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS))
        ->withLines([factory(PdkOrderLine::class)->withProduct('trunkrs-product-large')->withPrice(1000000)])
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe(200000);
});

it('carrier bounds: settings upTo below carrier min is raised to carrier min', function () {
    mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);

    // Carrier min=50000 means insurance is mandatory at ≥ €500.
    // Settings upTo=0 would normally yield 0, but carrier min forces 50000.
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL))
                ->push(
                    factory(Carrier::class)
                        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
                        ->withInsurance(50000, 50000, 200000)
                )
        )
        ->store();

    factory(Settings::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS, [
            CarrierSettings::EXPORT_INSURANCE                  => true,
            CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT      => 0,
            CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE => 100,
            CarrierSettings::EXPORT_INSURANCE_UP_TO            => 0,
        ])
        ->store();

    factory(PdkProduct::class)->withExternalIdentifier('bounds-product')->withPrice(100000)->store();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS))
        ->withLines([factory(PdkOrderLine::class)->withProduct('bounds-product')->withPrice(100000)])
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe(50000);
});

it('carrier bounds: settings percentage result below carrier min is raised to carrier min', function () {
    mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);

    // Carrier min=50000. Percentage 10% of €100 = €10 → tier 50000. Settings upTo allows it.
    // Without carrier min enforcement this would resolve to a lower tier.
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL))
                ->push(
                    factory(Carrier::class)
                        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
                        ->withInsurance(50000, 50000, 200000)
                )
        )
        ->store();

    factory(Settings::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS, [
            CarrierSettings::EXPORT_INSURANCE                  => true,
            CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT      => 0,
            CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE => 10,
            CarrierSettings::EXPORT_INSURANCE_UP_TO            => 200000,
        ])
        ->store();

    factory(PdkProduct::class)->withExternalIdentifier('pct-product')->withPrice(10000)->store();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS))
        ->withLines([factory(PdkOrderLine::class)->withProduct('pct-product')->withPrice(10000)])
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    // 10% of 10000 = 1000 → tier lookup yields 50000 (first tier ≥ 1000) → clamped to carrier min 50000
    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe(50000);
});

it('carrier bounds: explicit amount below carrier min is raised to carrier min', function () {
    mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL))
                ->push(
                    factory(Carrier::class)
                        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
                        ->withInsurance(50000, 50000, 200000)
                )
        )
        ->store();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
                ->withShipmentOptions(factory(ShipmentOptions::class)->withInsurance(1000))
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    // Explicit 1000 → tier 50000 → clamped to carrier min 50000
    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe(50000);
});

it('carrier bounds: explicit amount above carrier max is capped to carrier max', function () {
    mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL))
                ->push(
                    factory(Carrier::class)
                        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
                        ->withInsurance(0, 0, 100000)
                )
        )
        ->store();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
                ->withShipmentOptions(factory(ShipmentOptions::class)->withInsurance(500000))
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    // Explicit 500000 → tier 100000 (highest) → clamped to carrier max 100000
    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe(100000);
});

it('carrier bounds: inherit with insurance disabled and carrier min > 0 uses default clamped to min', function () {
    mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);

    // Carrier default=0, min=50000 → default is clamped to carrier min by clampToCarrierRange.
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL))
                ->push(
                    factory(Carrier::class)
                        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
                        ->withInsurance(0, 50000, 200000)
                )
        )
        ->store();

    factory(Settings::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS, [
            CarrierSettings::EXPORT_INSURANCE => false,
        ])
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    // INHERIT + insurance disabled → carrierDefault(0) clamped to [50000, 200000] = 50000
    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe(50000);
});

it('carrier bounds: settings fromAmount threshold still returns carrier min, not zero', function () {
    mockPdkProperty('orderCalculators', [InsuranceCalculator::class]);

    // Carrier min=50000. Order value (€50) is below fromAmount (€100).
    // Without bounds enforcement, this would return 0. With carrier min=50000, returns 50000.
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL))
                ->push(
                    factory(Carrier::class)
                        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS)
                        ->withInsurance(50000, 50000, 200000)
                )
        )
        ->store();

    factory(Settings::class)
        ->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS, [
            CarrierSettings::EXPORT_INSURANCE                  => true,
            CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT      => 100,
            CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE => 100,
            CarrierSettings::EXPORT_INSURANCE_UP_TO            => 200000,
        ])
        ->store();

    factory(PdkProduct::class)->withExternalIdentifier('low-product')->withPrice(5000)->store();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier(RefCapabilitiesSharedCarrierV2::TRUNKRS))
        ->withLines([factory(PdkOrderLine::class)->withProduct('low-product')->withPrice(5000)])
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    // orderPrice 5000 < fromAmount 10000 → would return 0, but carrier min=50000 takes precedence
    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe(50000);
});
