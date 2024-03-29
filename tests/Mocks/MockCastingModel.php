<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use DateTime;
use DateTimeInterface;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * @property Collection        $collection
 * @property DateTimeInterface $date
 * @property DateTimeInterface $dateFromArr
 * @property DateTimeInterface $datetime
 * @property float             $intFloat
 * @property string            $intString
 * @property string            $null
 * @property MockCastModel     $object
 * @property bool              $stringBool
 * @property bool              $stringFalseBool
 * @property int               $stringFalseInt
 * @property float             $stringFloat
 * @property int               $stringInt
 * @property bool              $stringTrueBool
 * @property int               $stringTrueInt
 * @property int               $timestamp
 */
class MockCastingModel extends Model
{
    protected $attributes = [
        'collection'        => [
            [
                'value' => 1,
            ],
            [
                'value' => 2,
            ],
        ],
        'object'            => ['property' => 'hello'],
        'date'              => '2022-01-10',
        'datetime'          => '2022-01-10 14:03:00',
        'date_from_arr'     => [
            'date'          => '2022-12-25 17:02:32.000000',
            'timezone_type' => 3,
            'timezone'      => 'Europe/Amsterdam',
        ],
        'timestamp'         => '2022-01-10 14:03:00',
        'string_bool'       => 'true',
        'string_false_bool' => 'false',
        'string_false_int'  => 'false',
        'string_int'        => '4',
        'string_true_bool'  => 'true',
        'string_true_int'   => 'true',
        'int_string'        => 1234,
        'int_float'         => 2,
        'string_float'      => '2',
        'without_a_cast'    => 'whatever',
        'null'              => null,
        'tristate1'         => TriStateService::ENABLED,
        'tristate2'         => TriStateService::INHERIT,
        'tristateCoerced1'  => 1000,
        'tristateCoerced2'  => TriStateService::INHERIT,
        'tristateString1'   => 'hello',
        'tristateString2'   => TriStateService::INHERIT,
    ];

    protected $casts      = [
        'collection'        => Collection::class,
        'object'            => MockCastModel::class,
        'date'              => 'date',
        'datetime'          => 'datetime',
        'date_from_arr'     => DateTime::class,
        'timestamp'         => 'timestamp',
        'string_bool'       => 'bool',
        'string_false_bool' => 'bool',
        'string_false_int'  => 'int',
        'string_int'        => 'int',
        'string_true_bool'  => 'bool',
        'string_true_int'   => 'int',
        'int_string'        => 'string',
        'int_float'         => 'float',
        'string_float'      => 'float',
        'null'              => 'string',
        'tristate1'         => TriStateService::TYPE_STRICT,
        'tristate2'         => TriStateService::TYPE_STRICT,
        'tristateCoerced1'  => TriStateService::TYPE_COERCED,
        'tristateCoerced2'  => TriStateService::TYPE_COERCED,
        'tristateString1'   => TriStateService::TYPE_STRING,
        'tristateString2'   => TriStateService::TYPE_STRING,
    ];
}
