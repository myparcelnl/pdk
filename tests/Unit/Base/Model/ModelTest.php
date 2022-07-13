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
        'mutateMe'   => null,
        'mutated'    => null,
    ];

    public function getMutatedAttribute(): string
    {
        return 'mutated';
    }

    public function setMutateMeAttribute($value): self
    {
        $this->attributes['mutateMe'] = "mutated_$value";
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
        'mutateMe'   => 'mutated_',
        'mutated'    => 'mutated',
    ]);
});

it('can use toSnakeCaseArray', function () {
    expect((new MyModel())->toSnakeCaseArray())->toEqual([
        'my_property' => 1,
        'mutate_me'   => 'mutated_',
        'mutated'     => 'mutated',
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
    $model = new MyModel(['mutated' => 'random']);
    expect($model->mutated)->toEqual('mutated');
});

it('supports set mutators', function () {
    $model = new MyModel(['mutateMe' => 'random']);
    expect($model->mutateMe)->toEqual('mutated_random');
});
