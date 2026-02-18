<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Base\Exception;

use MyParcelNL\Pdk\Base\Exception\ModelNotFoundException;
use RuntimeException;

it('extends RuntimeException', function () {
    $exception = new ModelNotFoundException('TestModel');

    expect($exception)->toBeInstanceOf(RuntimeException::class);
});

it('creates exception with model class name only', function () {
    $exception = new ModelNotFoundException('App\Models\User');

    expect($exception->getMessage())->toBe('No query results for model [App\Models\User]');
    expect($exception->getModelClassName())->toBe('App\Models\User');
    expect($exception->getIds())->toBe([]);
});

it('creates exception with model class name and single id', function () {
    $exception = new ModelNotFoundException('App\Models\Order', ['123']);

    expect($exception->getMessage())->toBe('No query results for model [App\Models\Order] with ID(s): 123');
    expect($exception->getModelClassName())->toBe('App\Models\Order');
    expect($exception->getIds())->toBe(['123']);
});

it('creates exception with model class name and multiple ids', function () {
    $exception = new ModelNotFoundException('App\Models\Product', ['1', '2', '3']);

    expect($exception->getMessage())->toBe('No query results for model [App\Models\Product] with ID(s): 1, 2, 3');
    expect($exception->getIds())->toBe(['1', '2', '3']);
});

it('accepts custom error code', function () {
    $exception = new ModelNotFoundException('TestModel', [], 404);

    expect($exception->getCode())->toBe(404);
});

it('accepts previous exception', function () {
    $previous  = new \Exception('Previous error');
    $exception = new ModelNotFoundException('TestModel', [], 0, $previous);

    expect($exception->getPrevious())->toBe($previous);
});

it('allows changing model class name via setter', function () {
    $exception = new ModelNotFoundException('OriginalModel', ['1']);

    $result = $exception->setModelClassName('NewModel');

    expect($result)->toBe($exception);
    expect($exception->getModelClassName())->toBe('NewModel');
    expect($exception->getMessage())->toBe('No query results for model [NewModel] with ID(s): 1');
});

it('updates message correctly when setting model class name without ids', function () {
    $exception = new ModelNotFoundException('OriginalModel');

    $exception->setModelClassName('UpdatedModel');

    expect($exception->getMessage())->toBe('No query results for model [UpdatedModel]');
});
