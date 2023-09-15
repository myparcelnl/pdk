<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTimeImmutable;
use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string             $cutoffTime
 * @property null|\DateTimeImmutable $date
 * @property null|bool               $dispatch
 * @property null|string             $sameDayCutoffTime
 * @property int                     $weekday
 */
class DropOffDay extends Model
{
    final public const WEEKDAY_MONDAY    = 1;
    final public const WEEKDAY_TUESDAY   = 2;
    final public const WEEKDAY_WEDNESDAY = 3;
    final public const WEEKDAY_THURSDAY  = 4;
    final public const WEEKDAY_FRIDAY    = 5;
    final public const WEEKDAY_SATURDAY  = 6;
    final public const WEEKDAY_SUNDAY    = 0;
    final public const WEEKDAYS          = [
        self::WEEKDAY_MONDAY,
        self::WEEKDAY_TUESDAY,
        self::WEEKDAY_WEDNESDAY,
        self::WEEKDAY_THURSDAY,
        self::WEEKDAY_FRIDAY,
        self::WEEKDAY_SATURDAY,
        self::WEEKDAY_SUNDAY,
    ];

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
