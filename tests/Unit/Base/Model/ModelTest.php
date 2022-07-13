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
    ];
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
    expect((new MyModel())->toArray())->toEqual(['my_property' => 1]);
});
