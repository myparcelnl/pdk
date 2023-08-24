<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Operation;

use InvalidArgumentException;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Context\Model\OrderDataContext;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;

it('can be converted to array', function (string $id) {
    $operation = new FormFetchContextOperation(new FormOperationBuilder(), $id);

    expect($operation->toArray())->toBe([
        '$fetchContext' => [
            '$id' => $id,
        ],
    ]);
})->with(Context::ALL);

it('throws error if passed string is not a valid context', function () {
    new FormFetchContextOperation(new FormOperationBuilder(), 'invalid');
})->throws(InvalidArgumentException::class);

it('can use conditions', function () {
    $operation = new FormFetchContextOperation(new FormOperationBuilder(), OrderDataContext::ID);

    $operation
        ->if('target')
        ->eq('value');

    expect($operation->toArray())->toBe([
        '$fetchContext' => [
            '$id' => OrderDataContext::ID,
            '$if' => [
                [
                    '$target' => 'target',
                    '$eq'     => 'value',
                ],
            ],
        ],
    ]);
});
