<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

$data = [
    'value' => [
        'nested'  => 1,
        'nested2' => 2,
        'nested3' => [
            'level' => new Collection(),
        ],
        'nested4' => null,
        'nested5' => (object) ['property' => 5],
    ],
];

it('executes data_get', function ($key, $result) use ($data) {
    $helpers = new Helpers();

    expect($helpers->data_get($data, $key))->toEqual($result);
})->with([
    'nested' => [
        'key'    => 'value.nested',
        'result' => 1,
    ],

    'wildcard' => [
        'key'    => 'value.*',
        'result' => array_values($data['value']),
    ],

    'null key' => [
        'key'    => null,
        'result' => $data,
    ],

    'null segment' => [
        'key'    => ['value', null],
        'result' => $data['value'],
    ],

    'collection' => [
        'key'    => 'value.nested3.level.*',
        'result' => [],
    ],

    'wildcard on non-array' => [
        'key'    => 'value.nested2.*',
        'result' => null,
    ],

    'nonexistent key' => [
        'key'    => 'value.nested2.another',
        'result' => null,
    ],

    'key in object' => [
        'key'    => 'value.nested5.property',
        'result' => 5,
    ],
]);
