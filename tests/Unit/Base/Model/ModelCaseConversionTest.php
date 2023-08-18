<?php
/** @noinspection PhpUndefinedFieldInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Mock\Model\DifferentAttributeCasingModel;

it('can initialize and get properties with any case', function () {
    $model = new DifferentAttributeCasingModel([
        'snake_case' => 'snake_case',
    ]);

    $model->camelCase    = 'camelCase';
    $model['StudlyCase'] = 'StudlyCase';

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
