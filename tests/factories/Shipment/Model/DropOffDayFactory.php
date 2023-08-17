<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTimeImmutable;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of DropOffDay
 * @method DropOffDay make()
 * @method $this withCutoffTime(string $cutoffTime)
 * @method $this withDate(string|DateTimeImmutable $date)
 * @method $this withDispatch(bool $dispatch)
 * @method $this withSameDayCutoffTime(string $sameDayCutoffTime)
 * @method $this withWeekday(int $weekday)
 */
final class DropOffDayFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return DropOffDay::class;
    }
}
