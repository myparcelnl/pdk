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

    $this->deliveryOptionsService = new class(
        Pdk::get(CartCalculationServiceInterface::class),
        Pdk::get(CapabilitiesValidationService::class),
        Pdk::get(CarrierRepositoryInterface::class),
        Pdk::get(CountryServiceInterface::class),
        Pdk::get(CurrencyServiceInterface::class),
        $this->dropOffService,
        Pdk::get(TaxServiceInterface::class)
    ) extends DeliveryOptionsService {
        public DateTimeImmutable $today;

        protected function getToday(): DateTimeImmutable
        {
            return $this->today;
        }
    };
    $this->deliveryOptionsService->today = $this->dropOffService->today;
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
    $this->deliveryOptionsService->today = $this->dropOffService->today;

    factory(CarrierSettings::class, $this->carrier->carrier)
        ->withDeliveryOptions()
        ->withDeliveryDaysWindow($window)
        ->withDropOffDelay($delay)
        ->withAllowMondayDelivery(false)
        ->withAllowStandardDelivery(true)
        ->withAllowEveningDelivery(true)
        ->withDropOffPossibilities(['dropOffDays' => $this->week, 'dropOffDaysDeviations' => []])
        ->store();

    $settings = Pdk::get(DeliveryOptionsServiceInterface::class)->createAllCarrierSettings($this->cart);
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

it('preserves the existing dispatch calculation for potentially relevant date overrides', function (
    array $deviations,
    array $expectedDays
) {
    factory(CarrierSettings::class, $this->carrier->carrier)
        ->withDeliveryOptions()
        ->withDeliveryDaysWindow(2)
        ->withDropOffDelay(0)
        ->withDropOffPossibilities([
            'dropOffDays'           => $this->week,
            'dropOffDaysDeviations' => $deviations,
        ])
        ->store();

    $settings = Pdk::get(DeliveryOptionsServiceInterface::class)->createAllCarrierSettings($this->cart);
    $output = $settings['carrierSettings'][FrontendData::getLegacyCarrierIdentifier($this->carrier->carrier)];

    expect($output['dropOffDays'])->toBe($expectedDays)
        ->and($output['deliveryDaysWindow'])->toBe(2)
        ->and($output['dropOffDelay'])->toBe(0)
        ->and($output['cutoffTime'])->toBe('15:00');
})->with([
    'closed Friday' => [[['date' => '2026-09-18', 'dispatch' => false]], [4, 1]],
    'closed Thursday' => [[['date' => '2026-09-17', 'dispatch' => false]], [5, 1]],
    'next week, beyond the two initially calculated dates' => [[['date' => '2026-09-24', 'dispatch' => false]], [4, 5]],
    'last day of the API horizon' => [[['date' => '2026-10-15', 'dispatch' => false]], [4, 5]],
    'last day with a time component' => [[['date' => '2026-10-15 23:59:59', 'dispatch' => false]], [4, 5]],
    'cutoff-only override' => [[['date' => '2026-09-17', 'dispatch' => null, 'cutoffTime' => '12:00']], [4, 5]],
    'override without a date' => [[['weekday' => 5, 'dispatch' => false]], [4, 5]],
    'old, relevant and distant overrides together' => [[
        ['date' => '2026-09-10', 'dispatch' => false],
        ['date' => '2026-09-18', 'dispatch' => false],
        ['date' => '2026-12-17', 'dispatch' => false],
    ], [4, 1]],
]);

it('keeps all weekly dispatch days when overrides cannot affect the current planning', function (
    array $deviations,
    int $delay,
    int $minimumDropOffDelay
) {
    factory(CarrierSettings::class, $this->carrier->carrier)
        ->withDeliveryOptions()
        ->withDeliveryDaysWindow(2)
        ->withDropOffDelay($delay)
        ->withDropOffPossibilities([
            'dropOffDays'           => $this->week,
            'dropOffDaysDeviations' => $deviations,
        ])
        ->store();

    $this->cart->shippingMethod->minimumDropOffDelay = $minimumDropOffDelay;
    $settings = $this->deliveryOptionsService->createAllCarrierSettings($this->cart);
    $output = $settings['carrierSettings'][FrontendData::getLegacyCarrierIdentifier($this->carrier->carrier)];

    expect($output['dropOffDays'])->toBe([1, 2, 3, 4, 5])
        ->and($output['deliveryDaysWindow'])->toBe(2)
        ->and($output['dropOffDelay'])->toBe(max($delay, $minimumDropOffDelay))
        ->and($output['cutoffTime'])->toBe('15:00');
})->with([
    'closure last week' => [[['date' => '2026-09-10', 'dispatch' => false]], 0, 0],
    'closure yesterday with a time component' => [[['date' => '2026-09-16 23:59:59', 'dispatch' => false]], 0, 0],
    'closure in three months' => [[['date' => '2026-12-17', 'dispatch' => false]], 0, 0],
    'first day outside the API horizon' => [[['date' => '2026-10-16', 'dispatch' => false]], 0, 0],
    'distant extra dispatch day' => [[['date' => '2026-12-19', 'dispatch' => true]], 0, 0],
    'distant cutoff override' => [[['date' => '2026-12-17', 'dispatch' => null, 'cutoffTime' => '12:00']], 0, 0],
    'old and distant closures together' => [[
        ['date' => '2026-09-10', 'dispatch' => false],
        ['date' => '2026-12-17', 'dispatch' => false],
    ], 0, 0],
    'carrier processing delay' => [[['date' => '2026-12-17', 'dispatch' => false]], 1, 0],
    'longer cart processing delay' => [[['date' => '2026-12-17', 'dispatch' => false]], 0, 14],
]);

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
        ->and($output['dropOffDays'])->toBe([1, 2, 3, 4, 5]);
});
