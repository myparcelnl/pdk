<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use DateTimeImmutable;
use MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Context\Model\DeliveryOptionsConfig;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection;
use MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;
use MyParcelNL\Pdk\Shipment\Service\DropOffService;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('checkout');

usesShared(new UsesEachMockPdkInstance(), new UsesAccountMock(), new UsesSdkApiMock());

beforeEach(function () {
    // Fix only the date; use the real dispatch calculations.
    $this->dropOffService = new class extends DropOffService {
        public DateTimeImmutable $today;

        public function getForDate(CarrierSettings $settings, DateTimeImmutable $date = null): ?DropOffDay
        {
            return parent::getForDate($settings, $date ?? $this->today);
        }

        public function getPossibleDropOffDays(
            CarrierSettings $settings,
            DateTimeImmutable $date = null
        ): DropOffDayCollection {
            return parent::getPossibleDropOffDays(
                $settings,
                $date ?? $this->today->modify("+$settings->dropOffDelay days")
            );
        }
    };

    $this->dropOffService->today = new DateTimeImmutable('2026-09-17');
    Pdk::set(DropOffServiceInterface::class, $this->dropOffService);

    $this->deliveryOptionsService = new DeliveryOptionsService(
        Pdk::get(CartCalculationServiceInterface::class),
        Pdk::get(CapabilitiesValidationService::class),
        Pdk::get(CarrierRepositoryInterface::class),
        Pdk::get(CountryServiceInterface::class),
        Pdk::get(CurrencyServiceInterface::class),
        $this->dropOffService,
        Pdk::get(TaxServiceInterface::class)
    );
    Pdk::set(DeliveryOptionsServiceInterface::class, $this->deliveryOptionsService);

    $this->carrier = Pdk::get(CarrierRepositoryInterface::class)->all()->first();
    $this->week = array_map(static function (int $day): array {
        return [
            'weekday'    => $day,
            'dispatch'   => $day >= 1 && $day <= 5,
            'cutoffTime' => $day === 6 ? '00:01' : '15:00',
        ];
    }, range(0, 6));
    $this->cart = new PdkCart([
        'lines' => [
            [
                'quantity' => 1,
                'product'  => ['weight' => 500, 'isDeliverable' => true],
            ],
        ],
    ]);
});

$cases = [];
foreach (range(14, 20) as $day) {
    foreach ([1, 2, 5, 14] as $window) {
        foreach ([0, 1] as $delay) {
            $date = "2026-09-$day";
            $cases["$date window=$window delay=$delay"] = [$date, $window, $delay];
        }
    }
}

it('keeps the weekly dispatch schedule independent of the delivery window', function (
    string $date,
    int $window,
    int $delay
) {
    $this->dropOffService->today = new DateTimeImmutable($date);

    factory(CarrierSettings::class, $this->carrier->carrier)
        ->withDeliveryOptions()
        ->withDeliveryDaysWindow($window)
        ->withDropOffDelay($delay)
        ->withAllowMondayDelivery(false)
        ->withAllowStandardDelivery(true)
        ->withAllowEveningDelivery(true)
        ->withDropOffPossibilities(['dropOffDays' => $this->week, 'dropOffDaysDeviations' => []])
        ->store();

    $settings = $this->deliveryOptionsService->createAllCarrierSettings($this->cart);
    $output = $settings['carrierSettings'][FrontendData::getLegacyCarrierIdentifier($this->carrier->carrier)];
    $days = $output['dropOffDays'];
    sort($days);

    expect($days)->toBe([1, 2, 3, 4, 5])
        ->and($output['deliveryDaysWindow'])->toBe($window)
        ->and($output['dropOffDelay'])->toBe($delay)
        ->and($output['cutoffTime'])->toBe((int) $this->dropOffService->today->format('N') <= 5 ? '15:00' : null)
        ->and($output['allowMondayDelivery'])->toBeFalse()
        ->and($output['allowStandardDelivery'])->toBeTrue()
        ->and($output['allowEveningDelivery'])->toBeTrue();
})->with($cases);

it('applies date overrides only to their own dispatch date', function (array $deviations, array $expectedDays) {
    factory(CarrierSettings::class, $this->carrier->carrier)
        ->withDeliveryOptions()
        ->withDeliveryDaysWindow(2)
        ->withDropOffDelay(0)
        ->withDropOffPossibilities([
            'dropOffDays'           => $this->week,
            'dropOffDaysDeviations' => $deviations,
        ])
        ->store();

    $settings = $this->deliveryOptionsService->createAllCarrierSettings($this->cart);
    $output = $settings['carrierSettings'][FrontendData::getLegacyCarrierIdentifier($this->carrier->carrier)];

    expect($output['dropOffDays'])->toEqualCanonicalizing($expectedDays)
        ->and($output['deliveryDaysWindow'])->toBe(2)
        ->and($output['dropOffDelay'])->toBe(0)
        ->and($output['cutoffTime'])->toBe('15:00');
})->with([
    // Today is Thursday 17 September 2026, so the checked dates are Thursday 17 up to and including Wednesday 23.
    'closed today' => [[['date' => '2026-09-17', 'dispatch' => false]], [5, 1, 2, 3]],
    'closed Friday this week' => [[['date' => '2026-09-18', 'dispatch' => false]], [4, 1, 2, 3]],
    'closed Monday next week' => [[['date' => '2026-09-21', 'dispatch' => false]], [4, 5, 2, 3]],
    'extra dispatch on Saturday' => [[['date' => '2026-09-19', 'dispatch' => true]], [4, 5, 6, 1, 2, 3]],
    'cutoff-only override' => [[['date' => '2026-09-18', 'dispatch' => null, 'cutoffTime' => '12:00']], [4, 5, 1, 2, 3]],
    'closure after the checked week' => [[['date' => '2026-09-24', 'dispatch' => false]], [4, 5, 1, 2, 3]],
    'closure last week' => [[['date' => '2026-09-10', 'dispatch' => false]], [4, 5, 1, 2, 3]],
    'closure yesterday with a time component' => [[['date' => '2026-09-16 23:59:59', 'dispatch' => false]], [4, 5, 1, 2, 3]],
    'closure in three months' => [[['date' => '2026-12-17', 'dispatch' => false]], [4, 5, 1, 2, 3]],
    'distant extra dispatch day' => [[['date' => '2026-12-19', 'dispatch' => true]], [4, 5, 1, 2, 3]],
    'old, relevant and distant overrides together' => [[
        ['date' => '2026-09-10', 'dispatch' => false],
        ['date' => '2026-09-18', 'dispatch' => false],
        ['date' => '2026-12-17', 'dispatch' => false],
    ], [4, 1, 2, 3]],
]);

it('keeps all weekly dispatch days with a processing delay', function (int $delay, int $minimumDropOffDelay) {
    factory(CarrierSettings::class, $this->carrier->carrier)
        ->withDeliveryOptions()
        ->withDeliveryDaysWindow(2)
        ->withDropOffDelay($delay)
        ->withDropOffPossibilities([
            'dropOffDays'           => $this->week,
            'dropOffDaysDeviations' => [['date' => '2026-12-17', 'dispatch' => false]],
        ])
        ->store();

    $this->cart->shippingMethod->minimumDropOffDelay = $minimumDropOffDelay;
    $settings = $this->deliveryOptionsService->createAllCarrierSettings($this->cart);
    $output = $settings['carrierSettings'][FrontendData::getLegacyCarrierIdentifier($this->carrier->carrier)];

    expect($output['dropOffDays'])->toEqualCanonicalizing([1, 2, 3, 4, 5])
        ->and($output['deliveryDaysWindow'])->toBe(2)
        ->and($output['dropOffDelay'])->toBe(max($delay, $minimumDropOffDelay))
        ->and($output['cutoffTime'])->toBe('15:00');
})->with([
    'carrier processing delay' => [1, 0],
    'longer cart processing delay' => [0, 14],
]);

it('excludes a closed weekday for the whole window, because the API only accepts weekdays', function () {
    factory(CarrierSettings::class, $this->carrier->carrier)
        ->withDeliveryOptions()
        ->withDeliveryDaysWindow(5)
        ->withDropOffDelay(0)
        ->withDropOffPossibilities([
            'dropOffDays'           => $this->week,
            'dropOffDaysDeviations' => [['date' => '2026-09-18', 'dispatch' => false]],
        ])
        ->store();

    $settings = $this->deliveryOptionsService->createAllCarrierSettings($this->cart);
    $output = $settings['carrierSettings'][FrontendData::getLegacyCarrierIdentifier($this->carrier->carrier)];

    // Friday 25 September is open, but excluding Friday is the only way to exclude Friday 18 September.
    expect($output['dropOffDays'])->toEqualCanonicalizing([4, 1, 2, 3]);
});

it('keeps general closed days separate from the weekly dispatch schedule', function () {
    $closedDays = ['2026-09-18', '2026-12-17'];
    factory(CarrierSettings::class, $this->carrier->carrier)
        ->withDeliveryOptions()
        ->withDeliveryDaysWindow(2)
        ->withDropOffPossibilities([
            'dropOffDays'           => $this->week,
            'dropOffDaysDeviations' => [['date' => '2026-09-10', 'dispatch' => false]],
        ])
        ->store();
    factory(CheckoutSettings::class)->withClosedDays($closedDays)->store();

    $config = DeliveryOptionsConfig::fromCart($this->cart);
    $output = $config->carrierSettings[FrontendData::getLegacyCarrierIdentifier($this->carrier->carrier)];

    expect($config->closedDays)->toBe($closedDays)
        ->and($output['dropOffDays'])->toEqualCanonicalizing([1, 2, 3, 4, 5]);
});
