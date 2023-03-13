<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Base\Model\Model;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Tests\Mocks\MockNestedModel;

const MODEL_DATA = [
    'my_value' => 1,
    'myModel'  => [
        'myModel' => [
            'my_value' => null,
        ],
    ],
];

it('can use toArray', function () {
    expect((new MockNestedModel(MODEL_DATA))->toArray())->toEqual([
        'myValue' => 1,
        'myModel' => [
            'myValue' => null,
            'myModel' => [
                'myValue' => null,
                'myModel' => null,
            ],
        ],
    ]);
});

it('can use toSnakeCaseArray', function () {
    expect((new MockNestedModel(MODEL_DATA))->toSnakeCaseArray())->toEqual([
        'my_value' => 1,
        'my_model' => [
            'my_value' => null,
            'my_model' => [
                'my_value' => null,
                'my_model' => null,
            ],
        ],
    ]);
});

it('can use toKebabCaseArray', function () {
    expect((new MockNestedModel(MODEL_DATA))->toKebabCaseArray())->toEqual([
        'my-value' => 1,
        'my-model' => [
            'my-value' => null,
            'my-model' => [
                'my-value' => null,
                'my-model' => null,
            ],
        ],
    ]);
});

it('can use toStudlyCaseArray', function () {
    expect(
        (new MockNestedModel(MODEL_DATA))->toStudlyCaseArray()
    )->toEqual([
        'MyValue' => 1,
        'MyModel' => [
            'MyValue' => null,
            'MyModel' => [
                'MyValue' => null,
                'MyModel' => null,
            ],
        ],
    ]);
});

it('can use toArrayWithoutNull', function () {
    expect((new MockNestedModel(MODEL_DATA))->toArrayWithoutNull())->toEqual([
        'myValue' => '1',
        'myModel' => [
            'myModel' => [],
        ],
    ]);
});

it('can combine case and skipping null', function () {
    expect((new MockNestedModel(MODEL_DATA))->toArray(Arrayable::SKIP_NULL | Arrayable::CASE_KEBAB))->toEqual([
        'my-value' => '1',
        'my-model' => [
            'my-model' => [],
        ],
    ]);
});
