<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\ConfigInterface;
use MyParcelNL\Sdk\src\Support\Arr;

class MockSettings implements ConfigInterface
{
    const SETTINGS = [
        'settings' => [
            'dropOffDays'          => [
                [
                    'date'              => '2022-01-01 00:00:00', // zaterdag
                    'cutoffTime'        => '15:30',
                    'sameDayCutoffTime' => '10:00',
                ],
                [
                    'date'       => '2022-01-03 00:00:00', //maandag
                    'cutoffTime' => '17:00',
                ],
                [
                    'date'              => '2022-01-04 00:00:00',
                    'cutoffTime'        => '17:00',
                    'sameDayCutoffTime' => '10:00',
                ],
                [
                    'date'              => '2022-01-05 00:00:00',
                    'cutoffTime'        => '17:00',
                    'sameDayCutoffTime' => '10:00',
                ],
                [
                    'date'              => '2022-01-06 00:00:00',
                    'cutoffTime'        => '17:00',
                    'sameDayCutoffTime' => '10:00',
                ],
                [
                    'date'              => '2022-01-07 00:00:00',
                    'cutoffTime'        => '17:00',
                    'sameDayCutoffTime' => '10:00',
                ],
                [
                    'date'       => '2022-01-08 00:00:00', // zaterdag
                    'cutoffTime' => '15:30',
                ],
                [
                    'date'              => '2022-01-10 00:00:00', //maandag
                    'cutoffTime'        => '17:00',
                    'sameDayCutoffTime' => '10:00',
                ],
                [
                    'date'              => '2022-01-11 00:00:00',
                    'cutoffTime'        => '17:00',
                    'sameDayCutoffTime' => '10:00',
                ],
                [
                    'date'              => '2022-01-12 00:00:00',
                    'cutoffTime'        => '17:00',
                    'sameDayCutoffTime' => '10:00',
                ],
                [
                    'date'              => '2022-01-13 00:00:00',
                    'cutoffTime'        => '17:00',
                    'sameDayCutoffTime' => '10:00',
                ],
                [
                    'date'              => '2022-01-14 00:00:00',
                    'cutoffTime'        => '17:00',
                    'sameDayCutoffTime' => '10:00',
                ],
            ],
            'dropOffDaysException' => [
                [
                    'date'              => '2022-01-04 00:00:00',
                    'cutoffTime'        => '20:00',
                    'sameDayCutoffTime' => '09:30',
                ],
                [
                    'date'     => '2022-01-05 00:00:00',
                    'dispatch' => false,
                ],
            ],
            'dropOffDelay'                   => 0,
            'deliveryDaysWindow'             => 7,
        ],
    ];

    public function get(string $key)
    {
        return Arr::get(self::SETTINGS, $key);
    }
}
