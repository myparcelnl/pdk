<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Service\DeliveryDateService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

it('returns today when fed nonsense', function() {
    expect(DeliveryDateService::fixPastDeliveryDate('quiche lorraine'))
        ->toEqual(date('Y-m-d', strtotime('+1 days')) . ' 00:00:00');
});

it('calculates the correct delivery date', function ($input, $output) {
    expect(DeliveryDateService::fixPastDeliveryDate($input))->toEqual($output);
})->with([
    'delivery date 1 day the future'  => [
        'input'  => date('Y-m-d', strtotime('+1 day')),
        'output' => date('Y-m-d', strtotime('+1 day')) . ' 00:00:00',
    ],
    'delivery date 1 week the future' => [
        'input'  => date('Y-m-d', strtotime('+1 week')),
        'output' => date('Y-m-d', strtotime('+1 week')) . ' 00:00:00',
    ],
    'delivery date in the past'       => [
        'input'  => '2022-07-08T00:00:00.000Z',
        'output' => date('Y-m-d', strtotime('+1 days')) . ' 00:00:00',
    ],
    'delivery date as object'         => [
        'input'  => new DateTime('-1 week'),
        'output' => date('Y-m-d', strtotime('+1 days')) . ' 00:00:00',
    ],
]);

it('returns correct delivery days', function () {
    PdkFactory::create(MockPdkConfig::DEFAULT_CONFIG);
    $deliveryDays = DeliveryDateService::getDeliveryDays(new DateTime('2022-01-02 00:00:00'));
    expect(
        array_filter(Arr::dot(array_values($deliveryDays->toArray())), function ($item) { return $item !== null; })
    )->toEqual(
        [
            '0.date'              => '2022-01-01 00:00:00',
            '0.cutoffTime'        => '15:30',
            '0.sameDayCutoffTime' => '10:00',
            '0.dispatch'          => true,
            '1.date'              => '2022-01-03 00:00:00',
            '1.cutoffTime'        => '17:00',
            '1.dispatch'          => true,
            '2.date'              => '2022-01-06 00:00:00',
            '2.cutoffTime'        => '17:00',
            '2.sameDayCutoffTime' => '10:00',
            '2.dispatch'          => true,
            '3.date'              => '2022-01-07 00:00:00',
            '3.cutoffTime'        => '17:00',
            '3.sameDayCutoffTime' => '10:00',
            '3.dispatch'          => true,
            '4.date'              => '2022-01-08 00:00:00',
            '4.cutoffTime'        => '15:30',
            '4.dispatch'          => true,
            '5.date'              => '2022-01-10 00:00:00',
            '5.cutoffTime'        => '17:00',
            '5.sameDayCutoffTime' => '10:00',
            '5.dispatch'          => true,
            '6.date'              => '2022-01-11 00:00:00',
            '6.cutoffTime'        => '17:00',
            '6.sameDayCutoffTime' => '10:00',
            '6.dispatch'          => true,
        ]
    );
});
