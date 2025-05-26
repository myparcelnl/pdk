<?php
/** @noinspection PhpUnused,PhpUndefinedMethodInspection,PhpUndefinedFieldInspection,StaticClosureCanBeUsedInspection,PhpIllegalPsrClassPathInspection,PhpMultipleClassesDeclarationsInOneFile,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use DateTime;
use DateTimeImmutable;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Mocks\ClassWithGuardedAttributes;
use MyParcelNL\Pdk\Tests\Mocks\InvalidCastingModel;
use MyParcelNL\Pdk\Tests\Mocks\MockCastingModel;
use MyParcelNL\Pdk\Tests\Mocks\MockCastModel;
use MyParcelNL\Pdk\Tests\Mocks\MockMutateModel;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use Psr\Log\LoggerInterface;
use function expect;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('model');

usesShared(new UsesMockPdkInstance());

it('casts attributes to classes', function () {
    $model = new MockCastingModel();

    expect($model->collection)
        ->toBeInstanceOf(Collection::class)
        ->and($model->object)
        ->toBeInstanceOf(MockCastModel::class)
        ->and($model->date)
        ->toBeInstanceOf(DateTimeImmutable::class)
        ->and($model->datetime)
        ->toBeInstanceOf(DateTimeImmutable::class);
});

it('casts attributes to primitives', function ($property, $assertion) {
    $model = new MockCastingModel();

    expect($model[$property])->{$assertion}();
})->with([
    'String to int'        => ['stringInt', 'toBeInt'],
    'String true to int'   => ['stringTrueInt', 'toBeInt'],
    'String false to int'  => ['stringFalseInt', 'toBeInt'],
    'String to bool'       => ['stringBool', 'toBeBool'],
    'String true to bool'  => ['stringTrueBool', 'toBeBool'],
    'String false to bool' => ['stringFalseBool', 'toBeBool'],
    'Int to string'        => ['intString', 'toBeString'],
    'Int to float'         => ['intFloat', 'toBeFloat'],
    'String to float'      => ['stringFloat', 'toBeFloat'],
]);

it('casts everything properly to array', function () {
    $model = new MockCastingModel();

    expect($model->attributesToArray())->toBe([
        'collection'       => [
            [
                'value' => 1,
            ],
            [
                'value' => 2,
            ],
        ],
        'object'           => [
            'property' => 'hello',
        ],
        'date'             => '2022-01-10 00:00:00',
        'datetime'         => '2022-01-10 14:03:00',
        'dateFromArr'      => '2022-12-25 17:02:32',
        'timestamp'        => 1641819780,
        'stringBool'       => true,
        'stringFalseBool'  => false,
        'stringFalseInt'   => 0,
        'stringInt'        => 4,
        'stringTrueBool'   => true,
        'stringTrueInt'    => 1,
        'intString'        => '1234',
        'intFloat'         => 2.0,
        'stringFloat'      => 2.0,
        'withoutACast'     => 'whatever',
        'null'             => null,
        'tristate1'        => TriStateService::ENABLED,
        'tristate2'        => TriStateService::INHERIT,
        'tristateCoerced1' => 1000,
        'tristateCoerced2' => TriStateService::INHERIT,
        'tristateString1'  => 'hello',
        'tristateString2'  => TriStateService::INHERIT,
    ]);
});

it('can use casted properties', function () {
    $model = new MockCastingModel();

    $model->object->property = 'pen';

    expect($model->object)
        ->toBeInstanceOf(MockCastModel::class)
        ->and($model['object'])
        ->toBeInstanceOf(MockCastModel::class)
        ->and($model->getObject())
        ->toBeInstanceOf(MockCastModel::class);
});

it('returns null and logs error on invalid cast', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);
    $value  = new DateTime();

    $model = new InvalidCastingModel(['value' => $value]);

    $result = $model->toArray();
    $logs   = $logger->getLogs();

    expect($result['value'])
        ->toBeNull()
        ->and($logs)
        ->toHaveLength(1)
        ->and($logs[0]['message'])
        ->toContain(sprintf('Failed to cast "value" to "%s"', MockCastModel::class));
});

it('gets only requested elements', function () {
    $model = new MockMutateModel();

    expect($model->only(['bloemkool', 'perenboom']))->toHaveKeys(['bloemkool', 'perenboom']);
});

it('gets only requested elements with string', function () {
    $model = new MockMutateModel();

    expect($model->only('myProperty'))->toHaveKeys(['myProperty']);
});

it('checks if guarded properties cannot be modified', function () {
    $model = new ClassWithGuardedAttributes(['field' => 1]);

    $model['field'] = 2;
    $model->setField(3);
    $model->fill(['field' => 4]);
    $model->field = 5;

    expect($model->field)->toEqual('test');
});

it('can cast various datetime formats', function (string $input, string $expected) {
    $model = new MockCastingModel([
        'datetime' => $input,
    ]);

    $array = $model->toArrayWithoutNull();

    expect($array['datetime'])->toBe($expected);
})->with(function () {
    return [
        'Y-m-d'          => ['2077-10-23', '2077-10-23 00:00:00'],
        'Y-m-d H:i:s'    => ['2077-10-23 09:45:56', '2077-10-23 09:45:56'],
        'ATOM, RFC3339'  => ['2077-10-23T09:45:56+01:00', '2077-10-23 09:45:56'],
        'ISO8601'        => ['2077-10-23T09:45:56+0100', '2077-10-23 09:45:56'],
        'ISO8601 with Z' => ['2077-10-23T09:45:56Z', '2077-10-23 09:45:56'],
        'Y-m-d H:i:s.u'  => ['2077-10-23 09:45:56.123456', '2077-10-23 09:45:56'],
    ];
});
