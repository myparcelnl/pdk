<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use BadMethodCallException;
use InvalidArgumentException;

it('throws error when getting a non-existing property', function () {
    $formCondition = new FormCondition(new FormOperationBuilder());

    /** @noinspection PhpUndefinedFieldInspection */
    $formCondition->propertyThatDoesNotExist;
})->throws(InvalidArgumentException::class);

it('throws error when setting a property', function () {
    $formCondition = new FormCondition(new FormOperationBuilder());

    /** @noinspection PhpUndefinedFieldInspection */
    $formCondition->property = 'foo';
})->throws(BadMethodCallException::class);

it('returns false when checking if a property is set', function () {
    $formCondition = new FormCondition(new FormOperationBuilder());

    expect(isset($formCondition->property))
        ->toBeFalse()
        ->and(isset($formCondition->target))
        ->toBeFalse();
});

it('throws error when unsetting a property', function () {
    $formCondition = new FormCondition(new FormOperationBuilder());

    unset($formCondition->property);
})->throws(BadMethodCallException::class);

dataset('matchers', [
    'eq' => [
        'method' => 'eq',
        'args'   => ['foo'],
        'output' => [
            '$eq' => 'foo',
        ],
    ],

    'ne' => [
        'method' => 'ne',
        'args'   => ['foo'],
        'output' => [
            '$ne' => 'foo',
        ],
    ],

    'gt' => [
        'method' => 'gt',
        'args'   => ['foo'],
        'output' => [
            '$gt' => 'foo',
        ],
    ],

    'gte' => [
        'method' => 'gte',
        'args'   => ['foo'],
        'output' => [
            '$gte' => 'foo',
        ],
    ],

    'lt' => [
        'method' => 'lt',
        'args'   => ['foo'],
        'output' => [
            '$lt' => 'foo',
        ],
    ],

    'lte' => [
        'method' => 'lte',
        'args'   => ['foo'],
        'output' => [
            '$lte' => 'foo',
        ],
    ],

    'in' => [
        'method' => 'in',
        'args'   => [['foo', 'bar']],
        'output' => [
            '$in' => ['foo', 'bar'],
        ],
    ],

    'nin' => [
        'method' => 'nin',
        'args'   => [['foo', 'bar']],
        'output' => [
            '$nin' => ['foo', 'bar'],
        ],
    ],
]);

it('uses matchers', function (string $method, array $args, array $output) {
    $condition = new FormCondition(new FormOperationBuilder());

    $condition->{$method}(...$args);

    expect($condition->toArray())->toBe($output);
})->with('matchers');

it('can chain matchers with and', function (string $method, array $args, array $output) {
    $condition = new FormCondition(new FormOperationBuilder());

    $condition->eq('boo')->and->{$method}(...$args);

    expect($condition->toArray())->toBe([
        '$and' => [
            [
                '$eq' => 'boo',
            ],
            $output,
        ],
    ]);
})->with('matchers');

it('can chain matchers with or', function (string $method, array $args, array $output) {
    $condition = new FormCondition(new FormOperationBuilder());

    $condition->eq('boo')->or->{$method}(...$args);

    expect($condition->toArray())->toBe([
        '$or' => [
            [
                '$eq' => 'boo',
            ],
            $output,
        ],
    ]);
})->with('matchers');
