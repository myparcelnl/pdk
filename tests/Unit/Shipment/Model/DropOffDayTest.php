<?php
/** @noinspection PhpUnhandledExceptionInspection, StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings as SettingsModel;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

it(
    'returns correct delivery days using a specific date',
    function (string $date, array $overrides, array $expectation) {
        $pdk = PdkFactory::create(MockPdkConfig::create());

        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository $repository */
        $repository = $pdk->get(AbstractSettingsRepository::class);

        /** @var \MyParcelNL\Pdk\Settings\Model\CarrierSettings $carrier */
        $carrier = $repository->getSettings()->carrier->first();
        $repository->set(
            new SettingsModel([
                CarrierSettings::ID => [
                    [
                        CarrierSettings::DROP_OFF_POSSIBILITIES => array_replace_recursive(
                            $carrier->dropOffPossibilities->toArray(),
                            $overrides
                        ),
                    ],
                ],
            ])
        );

        /** @var \MyParcelNL\Pdk\Settings\Model\DropOffPossibilities $possibilities */
        $possibilities = Settings::get('carrier.0.dropOffPossibilities');
        $deliveryDays  = $possibilities->getPossibleDropOffDays(new DateTimeImmutable($date));

        expect(Arr::dot($deliveryDays->toArray()))->toEqual($expectation);
    }
)->with([
    'Monday, 3 Jan 2022' => [
        'date'        => '2022-01-03 00:00:00',
        'overrides'   => [
            'deliveryDaysWindow' => 3,
        ],
        'expectation' => [
            '0.cutoffTime'        => '17:00',
            '0.date'              => '2022-01-03 00:00:00',
            '0.dispatch'          => true,
            '0.sameDayCutoffTime' => null,
            '0.weekday'           => 1,
            '1.cutoffTime'        => '15:00',
            '1.date'              => '2022-01-04 00:00:00',
            '1.dispatch'          => true,
            '1.sameDayCutoffTime' => '10:00',
            '1.weekday'           => 2,
            '2.cutoffTime'        => '17:00',
            '2.date'              => '2022-01-05 00:00:00',
            '2.dispatch'          => true,
            '2.sameDayCutoffTime' => null,
            '2.weekday'           => 3,
        ],
    ],

    'Monday, 3 Jan 2022 and deviations' => [
        'date'        => '2022-01-03 00:00:00',
        'overrides'   => [
            'dropOffDaysDeviations' => [
                [
                    'cutoffTime'        => '20:00',
                    'date'              => '2022-01-04 00:00:00',
                    'dispatch'          => null,
                    'sameDayCutoffTime' => '09:30',
                ],
                [
                    'cutoffTime'        => null,
                    'date'              => '2022-01-05 00:00:00',
                    'dispatch'          => false,
                    'sameDayCutoffTime' => null,
                ],
                [
                    'cutoffTime'        => null,
                    'date'              => '2022-01-07 00:00:00',
                    'dispatch'          => null,
                    'sameDayCutoffTime' => '08:00',
                ],
                [
                    'cutoffTime'        => null,
                    'date'              => '2022-01-11 00:00:00',
                    'dispatch'          => null,
                    'sameDayCutoffTime' => '09:30',
                ],
            ],
        ],
        'expectation' => [
            '0.cutoffTime'        => '17:00',
            '0.date'              => '2022-01-03 00:00:00',
            '0.dispatch'          => true,
            '0.sameDayCutoffTime' => null,
            '0.weekday'           => DropOffDay::WEEKDAY_MONDAY,

            '1.cutoffTime'        => '20:00', // deviation
            '1.date'              => '2022-01-04 00:00:00',
            '1.dispatch'          => true, // default
            '1.sameDayCutoffTime' => '09:30', // deviation
            '1.weekday'           => DropOffDay::WEEKDAY_TUESDAY,

            // Wednesday the 5th is missing because it's in the deviations with dispatch=false.

            '2.cutoffTime'        => '15:00',
            '2.date'              => '2022-01-06 00:00:00',
            '2.dispatch'          => true,
            '2.sameDayCutoffTime' => '10:00',
            '2.weekday'           => DropOffDay::WEEKDAY_THURSDAY,

            '3.cutoffTime'        => '17:00',
            '3.date'              => '2022-01-07 00:00:00',
            '3.dispatch'          => true,
            '3.sameDayCutoffTime' => '08:00', // deviation
            '3.weekday'           => DropOffDay::WEEKDAY_FRIDAY,

            '4.cutoffTime'        => '15:30',
            '4.date'              => '2022-01-08 00:00:00',
            '4.dispatch'          => true,
            '4.sameDayCutoffTime' => '10:00',
            '4.weekday'           => DropOffDay::WEEKDAY_SATURDAY,

            // No Sunday

            '5.cutoffTime'        => '17:00',
            '5.date'              => '2022-01-10 00:00:00',
            '5.dispatch'          => true,
            '5.sameDayCutoffTime' => null,
            '5.weekday'           => DropOffDay::WEEKDAY_MONDAY,

            '6.cutoffTime'        => '15:00',
            '6.date'              => '2022-01-11 00:00:00',
            '6.dispatch'          => true,
            '6.sameDayCutoffTime' => '09:30',
            '6.weekday'           => DropOffDay::WEEKDAY_TUESDAY,
        ],
    ],
]);

$dataset = [
    'deliveryDaysWindow 0' => [
        'overrides'     => ['deliveryDaysWindow' => 1],
        'amountOfItems' => 1,
    ],
];

for ($i = 1; $i < 14; $i++) {
    $dataset["deliveryDaysWindow $i"] = [
        'overrides'     => ['deliveryDaysWindow' => $i],
        'amountOfItems' => $i,
    ];
}

dataset('deliveryDaysAmountDataset', $dataset);

it('returns correct amount of delivery days', function (array $overrides, int $amountOfItems) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository $repository */
    $repository = $pdk->get(AbstractSettingsRepository::class);

    /** @var \MyParcelNL\Pdk\Settings\Model\CarrierSettings $carrier */
    $carrier = $repository->getSettings()->carrier->first();
    $repository->set(
        new SettingsModel([
            CarrierSettings::ID => [
                [
                    CarrierSettings::DROP_OFF_POSSIBILITIES => array_replace_recursive(
                        $carrier->dropOffPossibilities->toArray(),
                        $overrides
                    ),
                ],
            ],
        ])
    );

    /** @var \MyParcelNL\Pdk\Settings\Model\DropOffPossibilities $possibilities */
    $possibilities = Settings::get('carrier.0.dropOffPossibilities');
    $deliveryDays  = $possibilities->getPossibleDropOffDays();

    expect($deliveryDays->toArray())->toHaveLength($amountOfItems);
})->with('deliveryDaysAmountDataset');

it('throws exception when drop off day does not have weekday or date', function () {
    new DropOffDay();
})->throws(InvalidArgumentException::class);
