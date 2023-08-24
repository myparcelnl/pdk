<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Operation;

use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;

it('can be converted to array', function () {
    $operation = new FormSetPropOperation(new FormOperationBuilder(), 'options', [
        [
            'label' => 'label',
            'value' => 'value',
        ],
    ]);

    expect($operation->toArray())->toBe([
        '$setProp' => [
            '$prop'  => 'options',
            '$value' => [
                [
                    'label' => 'label',
                    'value' => 'value',
                ],
            ],
        ],
    ]);
});

it('can target another element', function () {
    $operation = new FormSetPropOperation(new FormOperationBuilder(), 'subtext', 'hello');

    $operation->on('target');

    expect($operation->toArray())->toBe([
        '$setProp' => [
            '$prop'   => 'subtext',
            '$value'  => 'hello',
            '$target' => 'target',
        ],
    ]);
});

it('can use conditions', function () {
    $operation = new FormSetPropOperation(new FormOperationBuilder(), 'subtext', 'hello');

    $operation->on('target')
        ->if('target')
        ->eq('value');

    expect($operation->toArray())->toBe([
        '$setProp' => [
            '$prop'   => 'subtext',
            '$value'  => 'hello',
            '$target' => 'target',
            '$if'     => [
                [
                    '$target' => 'target',
                    '$eq'     => 'value',
                ],
            ],
        ],
    ]);
});
