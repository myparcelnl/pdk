<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Service\DeliveryDateService;

it('calculates the correct delivery date', function ($input, $output) {
    expect(DeliveryDateService::fixPastDeliveryDate($input))->toEqual($output);
})->with([
    'delivery date 1 day the future'  => [
        'input'  => date('Y-m-d', strtotime('+1 day')),
        'output' => new DateTimeImmutable(date('Y-m-d', strtotime('+1 day')) . ' 00:00:00'),
    ],
    'delivery date 1 week the future' => [
        'input'  => date('Y-m-d', strtotime('+1 week')),
        'output' => new DateTimeImmutable(date('Y-m-d', strtotime('+1 week')) . ' 00:00:00'),
    ],
    'delivery date in the past'       => [
        'input'  => '2022-07-08T00:00:00.000Z',
        'output' => new DateTimeImmutable(date('Y-m-d', strtotime('+1 days')) . ' 00:00:00'),
    ],
    'delivery date as object'         => [
        'input'  => new DateTime('-1 week'),
        'output' => new DateTimeImmutable(date('Y-m-d', strtotime('+1 days')) . ' 00:00:00'),
    ],
]);

it('returns today when fed nonsense', function () {
    expect(DeliveryDateService::fixPastDeliveryDate('quiche lorraine'))
        ->toEqual(new DateTimeImmutable(date('Y-m-d', strtotime('+1 days')) . ' 00:00:00'));
});
