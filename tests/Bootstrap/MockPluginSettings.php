<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\ConfigInterface;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;
use MyParcelNL\Sdk\src\Support\Arr;

class MockPluginSettings implements ConfigInterface
{
    private const DEFAULT_SETTINGS = [
        'delivery' => [
            'dropOffDayPossibilities' => [
                'dropOffDays'           => [
                    [
                        'cutoffTime'        => '17:00',
                        'sameDayCutoffTime' => null,
                        'weekday'           => DropOffDay::WEEKDAY_MONDAY,
                        'dispatch'          => true,
                    ],
                    [
                        'cutoffTime'        => '15:00',
                        'sameDayCutoffTime' => '10:00',
                        'weekday'           => DropOffDay::WEEKDAY_TUESDAY,
                        'dispatch'          => true,
                    ],
                    [
                        'cutoffTime'        => '17:00',
                        'sameDayCutoffTime' => null,
                        'weekday'           => DropOffDay::WEEKDAY_WEDNESDAY,
                        'dispatch'          => true,
                    ],
                    [
                        'cutoffTime'        => '15:00',
                        'sameDayCutoffTime' => '10:00',
                        'weekday'           => DropOffDay::WEEKDAY_THURSDAY,
                        'dispatch'          => true,
                    ],
                    [
                        'cutoffTime'        => '17:00',
                        'sameDayCutoffTime' => '09:00',
                        'weekday'           => DropOffDay::WEEKDAY_FRIDAY,
                        'dispatch'          => true,
                    ],
                    [
                        'cutoffTime'        => '15:30',
                        'sameDayCutoffTime' => '10:00',
                        'weekday'           => DropOffDay::WEEKDAY_SATURDAY,
                        'dispatch'          => true,
                    ],
                    [
                        'cutoffTime'        => null,
                        'sameDayCutoffTime' => null,
                        'weekday'           => DropOffDay::WEEKDAY_SUNDAY,
                        'dispatch'          => false,
                    ],
                ],
                'dropOffDaysDeviations' => [],
                'dropOffDelay'          => 1,
                'deliveryDaysWindow'    => 7,
            ],
        ],
    ];

    /**
     * @var array
     */
    private $settings;

    public function __construct(array $overrides = [])
    {
        $this->settings = self::DEFAULT_SETTINGS;

        foreach (Arr::dot($overrides) as $override => $value) {
            $this->set($override, $value);
        }
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return Arr::get($this->settings, $key);
    }

    /**
     * @param  string $key
     * @param         $value
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        Arr::set($this->settings, $key, $value);
    }
}
