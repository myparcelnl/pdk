<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationInterface;

it('builds form operation arrays', function (array $args) {
    $result = $args['builder']->build();

    expect($result)->toBe($args['output']);
})->with(function () {
    return [
        'simple setValue on self' => function () {
            return [
                'builder' => (new FormOperationBuilder())->setValue('foo'),

                'output' => [
                    [
                        '$setValue' => [
                            '$value' => 'foo',
                        ],
                    ],
                ],
            ];
        },

        'simple setValue on target' => function () {
            return [
                'builder' => (new FormOperationBuilder())
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
            ];
        },

        'setValue with condition on self' => function () {
            return [
                'builder' => (new FormOperationBuilder())
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
            ];
        },

        'setValue with condition on target' => function () {
            return [
                'builder' => (new FormOperationBuilder())
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
            ];
        },

        'setValue with and conditions' => function () {
            return [
                'builder' => (new FormOperationBuilder())
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
            ];
        },

        'setValue with or conditions' => function () {
            return [
                'builder' => (new FormOperationBuilder())
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
            ];
        },

        'setValue if in or not in' => function () {
            return [
                'builder' => (new FormOperationBuilder())
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
            ];
        },

        'readOnlyWhen' => function () {
            return [
                'builder' => (new FormOperationBuilder())
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
            ];
        },

        'visibleWhen' => function () {
            return [
                'builder' => (new FormOperationBuilder())
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
            ];
        },

        'visibleWhen with multiple targets' => function () {
            return [
                'builder' => (new FormOperationBuilder())
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
            ];
        },

        'visibleWhen without value' => function () {
            return [
                'builder' => (new FormOperationBuilder())
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
            ];
        },

        'multiple visibleWhen operations merged into one' => function () {
            $builder = new FormOperationBuilder();

            $builder->visibleWhen('foo')
                ->eq('bar');
            $builder->visibleWhen('bar')
                ->eq('baz');

            return [
                'builder' => $builder,
                'output'  => [
                    [
                        '$visibleWhen' => [
                            '$if' => [
                                [
                                    '$target' => 'foo',
                                    '$eq'     => 'bar',
                                ],
                                [
                                    '$target' => 'bar',
                                    '$eq'     => 'baz',
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        },

        'afterUpdate' => function () {
            return [
                'builder' => (new FormOperationBuilder())
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
            ];
        },

        'complex multiple setValues' => function () {
            return [
                'builder' => (new FormOperationBuilder())
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
            ];
        },

        'with inline callback' => function () {
            return [
                'builder' => (new FormOperationBuilder())
                    ->setValue('broccoli', static function (FormOperationInterface $builder) {
                        $builder->if('pannenkoek')
                            ->eq('groente');
                    }),

                'output' => [
                    [
                        '$setValue' => [
                            '$value' => 'broccoli',
                            '$if'    => [
                                [
                                    '$target' => 'pannenkoek',
                                    '$eq'     => 'groente',
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        },

        'if with inline callback' => function () {
            return [
                'builder' => (new FormOperationBuilder())->setValue('broccoli')
                    ->if('pannenkoek', static function (FormCondition $builder) {
                        $builder->eq('groente');
                    }),

                'output' => [
                    [
                        '$setValue' => [
                            '$value' => 'broccoli',
                            '$if'    => [
                                [
                                    '$target' => 'pannenkoek',
                                    '$eq'     => 'groente',
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        },

        'readOnlyWhen with inline callback' => function () {
            return [
                'builder' => (new FormOperationBuilder())->readOnlyWhen(
                    'target',
                    static function (FormOperationInterface $builder) {
                        $builder->if('pannenkoek')
                            ->eq('groente');
                    }
                ),

                'output' => [
                    [
                        '$readOnlyWhen' => [
                            '$if' => [
                                [
                                    '$target' => 'pannenkoek',
                                    '$eq'     => 'groente',
                                ],
                                [
                                    '$target' => 'target',
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        },
    ];
});
