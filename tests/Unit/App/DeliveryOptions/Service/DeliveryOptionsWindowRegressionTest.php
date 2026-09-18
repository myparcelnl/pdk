<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use DateTimeImmutable;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
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

it('preserves the existing dispatch calculation when date overrides are set', function (
    array $deviation,
    array $expectedDays
) {
    factory(CarrierSettings::class, $this->carrier->carrier)
        ->withDeliveryOptions()
        ->withDeliveryDaysWindow(2)
        ->withDropOffDelay(0)
        ->withDropOffPossibilities([
            'dropOffDays'           => $this->week,
            'dropOffDaysDeviations' => [$deviation],
        ])
        ->store();

    $settings = Pdk::get(DeliveryOptionsServiceInterface::class)->createAllCarrierSettings($this->cart);
    $output = $settings['carrierSettings'][FrontendData::getLegacyCarrierIdentifier($this->carrier->carrier)];

    expect($output['dropOffDays'])->toBe($expectedDays)
        ->and($output['deliveryDaysWindow'])->toBe(2)
        ->and($output['dropOffDelay'])->toBe(0)
        ->and($output['cutoffTime'])->toBe('15:00');
})->with([
    'closed Friday' => [['date' => '2026-09-18', 'dispatch' => false], [4, 1]],
    'closed Thursday' => [['date' => '2026-09-17', 'dispatch' => false], [5, 1]],
]);
