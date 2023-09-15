<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSubOperationBuilderInterface;

it('builds form operation arrays', function (array $args) {
    /** @var \MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder $builder */
    $builder = $args['builder'];
    $result  = $builder->build();

    expect($result)->toBe($args['output']);
})->with(fn() => [
    'readOnlyWhen' => fn() => [
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
    ],

    'disabledWhen' => fn() => [
        'builder' => (new FormOperationBuilder())
            ->disabledWhen('target', true),

        'output' => [
            [
                '$disabledWhen' => [
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

    'visibleWhen' => fn() => [
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
    ],

    'visibleWhen with string that happens to also be a callable' => fn() => [
        'builder' => (new FormOperationBuilder())
            ->visibleWhen('target', 'test'), // test is a function from pest

        'output' => [
            [
                '$visibleWhen' => [
                    '$if' => [
                        [
                            '$target' => 'target',
                            '$eq'     => 'test',
                        ],
                    ],
                ],
            ],
        ],
    ],

    'visibleWhen with multiple targets' => fn() => [
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
    ],

    'visibleWhen without value' => fn() => [
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
    ],

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

    'afterUpdate' => fn() => [
        'builder' => (new FormOperationBuilder())
            ->afterUpdate()
            ->setValue('groente')
            ->on('soep')
            ->if('bloemkool')
            ->eq('broccoli'),
        'output'  => [
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

    'with inline callback' => fn() => [
        'builder' => (new FormOperationBuilder())->afterUpdate(
            function (FormSubOperationBuilderInterface $builder) {
                $builder
                    ->setValue('groente')
                    ->on('soep')
                    ->if('bloemkool')
                    ->eq('broccoli');
            }
        ),
        'output'  => [
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

    'if with inline callback' => fn() => [
        'builder' => (new FormOperationBuilder())->afterUpdate(
            function (FormSubOperationBuilderInterface $builder) {
                $builder->setValue('broccoli')
                    ->if('pannenkoek', static function (FormConditionInterface $builder) {
                        $builder->eq('groente');
                    });
            }
        ),
        'output'  => [
            [
                '$afterUpdate' => [
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
            ],
        ],
    ],

    'readOnlyWhen with inline callback' => fn() => [
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
    ],

    'setProp' => fn() => [
        'builder' => (new FormOperationBuilder())
            ->afterUpdate(function (FormSubOperationBuilderInterface $builder) {
                $builder->setProp('foo', 'bar');
            }),

        'output' => [
            [
                '$afterUpdate' => [
                    [
                        '$setProp' => [
                            '$prop'  => 'foo',
                            '$value' => 'bar',
                        ],
                    ],
                ],
            ],
        ],
    ],
]);
