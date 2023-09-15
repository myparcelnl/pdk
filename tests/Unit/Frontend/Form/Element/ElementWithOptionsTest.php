<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

use MyParcelNL\Pdk\Frontend\Form\Element\Concern\ElementBuilderWithOptionsInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\Concern\HasOptions;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\InteractiveElementBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\FormBuilder;
use MyParcelNL\Pdk\Settings\Model\Settings;

it(
    'adds options via withOptions',
    function (array $options, int $flags, array $result, string $class, string $component) {
        /** @var InteractiveElementBuilderInterface|HasOptions $element */
        $element = new $class('test');

        $element->withOptions($options, $flags);

        $created = $element->make()
            ->toArray();

        expect($created)->toEqual(
            array_replace([
                'name'       => 'test',
                '$component' => $component,
            ], $result)
        );
    }
)
    ->with(function () {
        return [
            'plain array' => [
                'options' => ['broccoli', 'bloemkool'],
                'flags'   => 0,
                'result'  => [
                    'options' => [
                        ['label' => 'test_option_broccoli', 'value' => 'broccoli'],
                        ['label' => 'test_option_bloemkool', 'value' => 'bloemkool'],
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
                    'options' => [
                        ['label' => 'test_option_broccoli', 'value' => 'bc'],
                        ['label' => 'test_option_bloemkool', 'value' => 'bk'],
                    ],
                ],
            ],

            'include none' => [
                'options' => ['broccoli', 'bloemkool'],
                'flags'   => ElementBuilderWithOptionsInterface::ADD_NONE,
                'result'  => [
                    'options' => [
                        ['label' => '_none', 'value' => Settings::OPTION_NONE],
                        ['label' => 'test_option_broccoli', 'value' => 'broccoli'],
                        ['label' => 'test_option_bloemkool', 'value' => 'bloemkool'],
                    ],
                ],
            ],

            'include default' => [
                'options' => ['broccoli', 'bloemkool'],
                'flags'   => ElementBuilderWithOptionsInterface::ADD_DEFAULT,
                'result'  => [
                    'options' => [
                        ['label' => '_default', 'value' => Settings::OPTION_DEFAULT],
                        ['label' => 'test_option_broccoli', 'value' => 'broccoli'],
                        ['label' => 'test_option_bloemkool', 'value' => 'bloemkool'],
                    ],
                ],
            ],

            'plain label' => [
                'options' => ['broccoli', 'bloemkool'],
                'flags'   => ElementBuilderWithOptionsInterface::USE_PLAIN_LABEL,
                'result'  => [
                    'options' => [
                        ['plainLabel' => 'broccoli', 'value' => 'broccoli'],
                        ['plainLabel' => 'bloemkool', 'value' => 'bloemkool'],
                    ],
                ],
            ],

            'multiple flags' => [
                'options' => ['broccoli', 'bloemkool'],
                'flags'   => ElementBuilderWithOptionsInterface::USE_PLAIN_LABEL | ElementBuilderWithOptionsInterface::ADD_DEFAULT | ElementBuilderWithOptionsInterface::ADD_NONE,
                'result'  => [
                    'options' => [
                        ['label' => '_none', 'value' => Settings::OPTION_NONE],
                        ['label' => '_default', 'value' => Settings::OPTION_DEFAULT],
                        ['plainLabel' => 'broccoli', 'value' => 'broccoli'],
                        ['plainLabel' => 'bloemkool', 'value' => 'bloemkool'],
                    ],
                ],
            ],

            'sort asc' => [
                'options' => ['broccoli', 'bloemkool'],
                'flags'   => ElementBuilderWithOptionsInterface::ADD_DEFAULT | ElementBuilderWithOptionsInterface::ADD_NONE | ElementBuilderWithOptionsInterface::SORT_ASC,
                'result'  => [
                    'options' => [
                        ['label' => '_none', 'value' => Settings::OPTION_NONE],
                        ['label' => '_default', 'value' => Settings::OPTION_DEFAULT],
                        ['label' => 'test_option_broccoli', 'value' => 'broccoli'],
                        ['label' => 'test_option_bloemkool', 'value' => 'bloemkool'],
                    ],
                    'sort'    => ElementBuilderWithOptionsInterface::SORT_ASC_VALUE,
                ],
            ],

            'sort desc' => [
                'options' => ['broccoli', 'bloemkool', 'aardappel'],
                'flags'   => ElementBuilderWithOptionsInterface::SORT_DESC,
                'result'  => [
                    'options' => [
                        ['label' => 'test_option_broccoli', 'value' => 'broccoli'],
                        ['label' => 'test_option_bloemkool', 'value' => 'bloemkool'],
                        ['label' => 'test_option_aardappel', 'value' => 'aardappel'],
                    ],
                    'sort'    => ElementBuilderWithOptionsInterface::SORT_DESC_VALUE,
                ],
            ],
        ];
    })
    ->with('interactive elements with options');

it('adds sort via withSort', function (string $sort, string $class, string $component) {
    /** @var InteractiveElementBuilderInterface|HasOptions $element */
    $element = new $class('test');

    $element->withOptions(['a', 'b']);
    $element->withSort($sort);

    $created = $element->make()
        ->toArray();

    expect($created)->toEqual([
        'name'       => 'test',
        '$component' => $component,
        'options'    => [
            ['label' => 'test_option_a', 'value' => 'a'],
            ['label' => 'test_option_b', 'value' => 'b'],
        ],
        'sort'       => $sort,
    ]);
})
    ->with([ElementBuilderWithOptionsInterface::SORT_ASC_VALUE, ElementBuilderWithOptionsInterface::SORT_DESC_VALUE])
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
                    ['label' => 'one_default', 'value' => Settings::OPTION_DEFAULT],
                    ['label' => 'one_two_three_choose_option_a', 'value' => 'a'],
                    ['label' => 'one_two_three_choose_option_b', 'value' => 'b'],
                ],
            ],
        ]);
})->with('interactive elements with options');
