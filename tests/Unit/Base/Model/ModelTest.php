<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection,PhpMultipleClassesDeclarationsInOneFile,PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Model\Model;

trait InitTrait
{
    public function initializeInitTrait()
    {
        $this->myProperty = 1;
    }
}

class MyModel extends Model
{
    use InitTrait;

    protected $attributes = [
        'myProperty' => null,
        'perenboom'  => null,
        'bloemkool'  => null,
    ];

    public function getBloemkoolAttribute(): string
    {
        return 'bloemkool';
    }

    public function setPerenboomAttribute($value): self
    {
        $this->attributes['perenboom'] = "mutated_$value";
        return $this;
    }
}

it('initializes traits', function () {
    expect((new MyModel())->myProperty)->toBe(1);
});

it('can use property accessors', function () {
    $model             = new MyModel();
    $model->myProperty = 2;

    expect($model->myProperty)->toBe(2);
});

it('can use getters and setters', function () {
    $model = new MyModel();
    $model->setMyProperty(3);

    expect($model->getMyProperty())->toBe(3);
});

it('can use offset getters and setters', function () {
    $model               = new MyModel();
    $model['myProperty'] = 2;

    expect($model['myProperty'])->toBe(2);
});

it('can use isset on property', function () {
    $model = new MyModel();

    expect(isset($model->myProperty))
        ->toBeTrue()
        ->and(isset($model->otherProperty))
        ->toBeFalse();
});

it('can use isset on array offset', function () {
    $model = new MyModel();

    expect(isset($model['myProperty']))
        ->toBeTrue()
        ->and(isset($model['otherProperty']))
        ->toBeFalse();
});

it('can use unset on array offset', function () {
    $model = new MyModel();
    unset($model['myProperty']);

    expect(isset($model['myProperty']))
        ->toBeFalse();
});

it('can use toArray', function () {
    expect((new MyModel())->toArray())->toEqual([
        'myProperty' => 1,
        'perenboom'  => 'mutated_',
        'bloemkool'  => 'bloemkool',
    ]);
});

it('can use toSnakeCaseArray', function () {
    expect((new MyModel())->toSnakeCaseArray())->toEqual([
        'my_property' => 1,
        'perenboom'   => 'mutated_',
        'bloemkool'   => 'bloemkool',
    ]);
});

it('can initialize and get properties with any case', function () {
    $model = new Model();

    $model->snake_case = 'snake_case';
    $model->camelCase  = 'camelCase';
    $model->StudlyCase = 'StudlyCase';

    expect($model->getAttributes())
        ->toEqual([
            'snakeCase'  => 'snake_case',
            'camelCase'  => 'camelCase',
            'studlyCase' => 'StudlyCase',
        ])
        ->and($model->snakeCase)
        ->toEqual('snake_case')
        ->and($model->studly_case)
        ->toEqual('StudlyCase')
        ->and($model->CamelCase)
        ->toEqual('camelCase');
});

it('supports get mutators', function () {
    $model = new MyModel(['bloemkool' => 'random']);
    expect($model->bloemkool)->toEqual('bloemkool');
});

it('supports set mutators', function () {
    $model = new MyModel(['perenboom' => 'random']);
    expect($model->perenboom)->toEqual('mutated_random');
});

it('throws error when unknown attributes are passed', function () {
    new MyModel(['whaaaaat' => 'fiets']);
})->throws(InvalidArgumentException::class);

