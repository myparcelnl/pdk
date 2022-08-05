<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTimeImmutable;
use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property \DateTimeImmutable $date
 * @property null|string        $cutoffTime
 * @property null|boolean       $dispatch
 * @property null|string        $sameDayCutoffTime
 * @property int                $weekday
 */
class DropOffDay extends Model
{
    public const WEEKDAY_MONDAY    = 1;
    public const WEEKDAY_TUESDAY   = 2;
    public const WEEKDAY_WEDNESDAY = 3;
    public const WEEKDAY_THURSDAY  = 4;
    public const WEEKDAY_FRIDAY    = 5;
    public const WEEKDAY_SATURDAY  = 6;
    public const WEEKDAY_SUNDAY    = 0;

    protected $attributes = [
        'cutoffTime'        => null,
        'date'              => null,
        'dispatch'          => true,
        'sameDayCutoffTime' => null,
        'weekday'           => null,
    ];

    protected $casts      = [
        'cutoffTime'        => 'string',
        'date'              => DateTimeImmutable::class,
        'dispatch'          => 'boolean',
        'sameDayCutoffTime' => 'string',
        'weekday'           => 'int',
    ];

    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        if (! $this->attributes['date'] && null === $this->attributes['weekday']) {
            throw new InvalidArgumentException('Either date or weekday must be set.');
        }

        if ($this->attributes['date']) {
            $this->attributes['weekday'] = (int) $this->date->format('w');
        }
    }
}
