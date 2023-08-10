<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\BuilderInterface;

it('builds form operation arrays', function (BuilderInterface $input, array $output) {
    $result = $input->build();

    expect($result)->toBe($output);
})->with(function () {
    return [
        'simple setValue on self' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())->setValue('foo'),

            'output' => [
                [
                    '$setValue' => [
                        '$value' => 'foo',
                    ],
                ],
            ],
        ],

        'simple setValue on target' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())
                ->setValue('new')
                ->on('old'),

            'output' => [
                [
                    '$setValue' => [
                        '$value'  => 'new',
                        '$target' => 'old',
                    ],
                ],
            ],
        ],

        'setValue with condition on self' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())
                ->setValue('foo')
                ->if('foo')
                ->eq('foo'),

            'output' => [
                [
                    '$setValue' => [
                        '$value' => 'foo',
                        '$if'    => [
                            [
                                '$target' => 'foo',
                                '$eq'     => 'foo',
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'setValue with condition on target' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())
                ->setValue('new')
                ->if('old')
                ->eq('old'),

            'output' => [
                [
                    '$setValue' => [
                        '$value' => 'new',
                        '$if'    => [
                            [
                                '$target' => 'old',
                                '$eq'     => 'old',
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'setValue with and conditions' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())
                ->setValue('appelboom')
                ->if('a-setting')
                ->gt(1)->and->lt(10),

            'output' => [
                [
                    '$setValue' => [
                        '$value' => 'appelboom',
                        '$if'    => [
                            [
                                '$and' => [
                                    [
                                        '$target' => 'a-setting',
                                        '$gt'     => 1,
                                    ],
                                    [
                                        '$target' => 'a-setting',
                                        '$lt'     => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'setValue with or conditions' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())
                ->setValue('other-setting')
                ->if('some-setting')
                ->gte(1)->or->lte(10),

            'output' => [
                [
                    '$setValue' => [
                        '$value' => 'other-setting',
                        '$if'    => [
                            [
                                '$or' => [
                                    [
                                        '$target' => 'some-setting',
                                        '$gte'    => 1,
                                    ],
                                    [
                                        '$target' => 'some-setting',
                                        '$lte'    => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'setValue if in or not in' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())
                ->setValue('new')
                ->if('foo')
                ->in(['foo', 'bar'])->or->nin(['baz', 'qux']),

            'output' => [
                [
                    '$setValue' => [
                        '$value' => 'new',
                        '$if'    => [
                            [
                                '$or' => [
                                    [
                                        '$target' => 'foo',
                                        '$in'     => ['foo', 'bar'],
                                    ],
                                    [
                                        '$target' => 'foo',
                                        '$nin'    => ['baz', 'qux'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'readOnlyWhen' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())
                ->readOnlyWhen()
                ->ne('foo')->and->ne('bar'),

            'output' => [
                [
                    '$readOnlyWhen' => [
                        '$if' => [
                            [
                                '$and' => [
                                    ['$ne' => 'foo'],
                                    ['$ne' => 'bar'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'visibleWhen' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())
                ->visibleWhen('target', true),

            'output' => [
                [
                    '$visibleWhen' => [
                        '$if' => [
                            [
                                '$target' => 'target',
                                '$eq'     => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'visibleWhen with multiple targets' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())
                ->visibleWhen('target')
                ->and('other-target'),

            'output' => [
                [
                    '$visibleWhen' => [
                        '$if' => [
                            [
                                '$and' => [
                                    ['$target' => 'target'],
                                    ['$target' => 'other-target'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'visibleWhen without value' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())
                ->visibleWhen('target'),

            'output' => [
                [
                    '$visibleWhen' => [
                        '$if' => [
                            ['$target' => 'target'],
                        ],
                    ],
                ],
            ],
        ],

        'afterUpdate' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())
                ->afterUpdate()
                ->setValue('groente')
                ->on('soep')
                ->if('bloemkool')
                ->eq('broccoli'),

            'output' => [
                [
                    '$afterUpdate' => [
                        [
                            '$setValue' => [
                                '$value'  => 'groente',
                                '$target' => 'soep',
                                '$if'     => [['$target' => 'bloemkool', '$eq' => 'broccoli']],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'complex multiple setValues' => [
            'afterUpdateBuilder' => (new FormOperationBuilder())
                ->setValue('foo')
                ->if->eq('foo')
                ->then->setValue('bar')
                ->if('bar')
                ->ne('baz')
                ->and
                ->ne('qux')
                ->then->setValue('baz')
                ->then->setValue('qux'),

            'output' => [
                [
                    '$setValue' => [
                        '$value' => 'foo',
                        '$if'    => [
                            [
                                '$eq' => 'foo',
                            ],
                        ],
                    ],
                ],
                [
                    '$setValue' => [
                        '$value' => 'bar',
                        '$if'    => [
                            [
                                '$and' => [
                                    [
                                        '$target' => 'bar',
                                        '$ne'     => 'baz',
                                    ],
                                    [
                                        '$target' => 'bar',
                                        '$ne'     => 'qux',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    '$setValue' => [
                        '$value' => 'baz',
                    ],
                ],
                [
                    '$setValue' => [
                        '$value' => 'qux',
                    ],
                ],
            ],
        ],
    ];
});
