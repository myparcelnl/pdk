<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

use MyParcelNL\Pdk\Frontend\Form\Element\Concern\ElementBuilderWithOptionsInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\Concern\HasOptions;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\InteractiveElementBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\FormBuilder;

it('adds options', function (array $options, int $flags, array $result, string $class, string $component) {
    /** @var InteractiveElementBuilderInterface|HasOptions $element */
    $element = new $class('test');

    $element->withOptions($options, $flags);

    $created = $element->make()
        ->toArray();

    expect($created)->toEqual([
        'name'       => 'test',
        '$component' => $component,
        'options'    => $result,
    ]);
})
    ->with([
        'plain array' => [
            'options' => ['broccoli', 'bloemkool'],
            'flags'   => 0,
            'result'  => [
                [
                    'label' => 'test_option_broccoli',
                    'value' => 'broccoli',
                ],
                [
                    'label' => 'test_option_bloemkool',
                    'value' => 'bloemkool',
                ],
            ],
        ],

        'associative array' => [
            'options' => [
                'bc' => 'broccoli',
                'bk' => 'bloemkool',
            ],
            'flags'   => 0,
            'result'  => [
                [
                    'label' => 'test_option_bc',
                    'value' => 'broccoli',
                ],
                [
                    'label' => 'test_option_bk',
                    'value' => 'bloemkool',
                ],
            ],
        ],

        'plain array of associative arrays should not be transformed' => [
            'options' => [
                [
                    'label' => 'broccoli',
                    'value' => 'bc',
                ],
                [
                    'label' => 'bloemkool',
                    'value' => 'bk',
                ],
            ],
            'flags'   => 0,
            'result'  => [
                [
                    'label' => 'broccoli',
                    'value' => 'bc',
                ],
                [
                    'label' => 'bloemkool',
                    'value' => 'bk',
                ],
            ],
        ],

        'plain array of associative arrays can use flags' => [
            'options' => [
                [
                    'label' => 'broccoli',
                    'value' => 'bc',
                ],
                [
                    'label' => 'bloemkool',
                    'value' => 'bk',
                ],
            ],
            'flags'   => ElementBuilderWithOptionsInterface::ADD_DEFAULT,
            'result'  => [
                [
                    'label' => 'option_default',
                    'value' => -1,
                ],
                [
                    'label' => 'broccoli',
                    'value' => 'bc',
                ],
                [
                    'label' => 'bloemkool',
                    'value' => 'bk',
                ],
            ],
        ],

        'include none' => [
            'options' => ['broccoli', 'bloemkool'],
            'flags'   => ElementBuilderWithOptionsInterface::ADD_NONE,
            'result'  => [
                [
                    'label' => 'option_none',
                    'value' => -1,
                ],
                [
                    'label' => 'test_option_broccoli',
                    'value' => 'broccoli',
                ],
                [
                    'label' => 'test_option_bloemkool',
                    'value' => 'bloemkool',
                ],
            ],
        ],

        'include default' => [
            'options' => ['broccoli', 'bloemkool'],
            'flags'   => ElementBuilderWithOptionsInterface::ADD_DEFAULT,
            'result'  => [
                [
                    'label' => 'option_default',
                    'value' => -1,
                ],
                [
                    'label' => 'test_option_broccoli',
                    'value' => 'broccoli',
                ],
                [
                    'label' => 'test_option_bloemkool',
                    'value' => 'bloemkool',
                ],
            ],
        ],

        'plain label' => [
            'options' => ['broccoli', 'bloemkool'],
            'flags'   => ElementBuilderWithOptionsInterface::USE_PLAIN_LABEL,
            'result'  => [
                [
                    'plainLabel' => 'broccoli',
                    'value'      => 'broccoli',
                ],
                [
                    'plainLabel' => 'bloemkool',
                    'value'      => 'bloemkool',
                ],
            ],
        ],

        'multiple flags' => [
            'options' => ['broccoli', 'bloemkool'],
            'flags'   => ElementBuilderWithOptionsInterface::USE_PLAIN_LABEL | ElementBuilderWithOptionsInterface::ADD_DEFAULT | ElementBuilderWithOptionsInterface::ADD_NONE,
            'result'  => [
                [
                    'label' => 'option_none',
                    'value' => -1,
                ],
                [
                    'label' => 'option_default',
                    'value' => -1,
                ],
                [
                    'plainLabel' => 'broccoli',
                    'value'      => 'broccoli',
                ],
                [
                    'plainLabel' => 'bloemkool',
                    'value'      => 'bloemkool',
                ],
            ],
        ],
    ])
    ->with('interactive elements with options');

it('handles prefixes from the wrapping form builder', function (string $class, string $component) {
    $formBuilder = new FormBuilder(['one', 'two', 'three']);

    $formBuilder->add(
        (new $class('choose'))->withOptions(
            ['a', 'b'],
            ElementBuilderWithOptionsInterface::ADD_DEFAULT
        )
    );

    $built = $formBuilder->build()
        ->toArrayWithoutNull();

    expect($built)
        ->toHaveLength(1)
        ->and($built)
        ->toEqual([
            [
                'name'       => 'choose',
                '$component' => $component,
                'options'    => [
                    [
                        'label' => 'option_default',
                        'value' => -1,
                    ],
                    [
                        'label' => 'one_two_three_choose_option_a',
                        'value' => 'a',
                    ],
                    [
                        'label' => 'one_two_three_choose_option_b',
                        'value' => 'b',
                    ],
                ],
            ],
        ]);
})->with('interactive elements with options');
