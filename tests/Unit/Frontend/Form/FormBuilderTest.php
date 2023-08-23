<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormAfterUpdateBuilder;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\TextInput;

it('builds multiple elements', function () {
    $formBuilder = new FormBuilder();

    /** @var \MyParcelNL\Pdk\Frontend\Form\Element\Contract\InteractiveElementBuilderInterface $element */
    $elements = [
        new TextInput('test1'),
        new TextInput('test2'),
        new TextInput('test3'),
    ];

    $formBuilder->add(...$elements);

    $built = $formBuilder->build()
        ->toArrayWithoutNull();

    expect($built)
        ->toHaveLength(3)
        ->and($built)
        ->each->toHaveKeysAndValues([
            '$component' => Components::INPUT_TEXT,
        ]);
});

it('adds elements with callback', function () {
    $formBuilder = new FormBuilder();

    /** @var \MyParcelNL\Pdk\Frontend\Form\Element\Contract\InteractiveElementBuilderInterface $element */
    $elements = [
        new TextInput('test1'),
        new TextInput('test2'),
        new TextInput('test3'),
    ];

    $formBuilder->addWith(function (ElementBuilderInterface $builder) {
        $builder->withProp('test', 1);
    }, ...$elements);

    $built = $formBuilder->build()
        ->toArrayWithoutNull();

    expect($built)
        ->toHaveLength(3)
        ->and($built)
        ->each->toHaveKeysAndValues([
            '$component' => Components::INPUT_TEXT,
            'test'       => 1,
        ]);
});

it('builds interactive elements', function (string $class, string $component) {
    $formBuilder = new FormBuilder();

    /** @var \MyParcelNL\Pdk\Frontend\Form\Element\Contract\InteractiveElementBuilderInterface $element */
    $element = new $class('test');

    $formBuilder->add($element);

    $built = $formBuilder->build()
        ->toArrayWithoutNull();

    expect($built)
        ->toHaveLength(1)
        ->and($built)
        ->each->toHaveKeysAndValues([
            'name'       => 'test',
            '$component' => $component,
        ]);
})->with('interactive elements');

it('adds operation builders', function () {
    $formBuilder = new FormBuilder();

    $elements = [
        (new TextInput('test1'))->afterUpdate(function (FormAfterUpdateBuilder $builder) {
            $builder->setValue(2)
                ->on('test2');
        }),
        (new TextInput('test2'))->afterUpdate(function (FormAfterUpdateBuilder $builder) {
            $builder->setValue(3)
                ->on('test3');
        }),
        new TextInput('test3'),
    ];

    $formBuilder->add(...$elements);

    $built = $formBuilder->build()
        ->toArrayWithoutNull();

    $firstTwo = $built;
    $last     = array_pop($firstTwo);

    expect($built)
        ->toHaveLength(3)
        ->and($firstTwo)
        ->toHaveLength(2)
        ->and($firstTwo)->each->toHaveKey('$builders')
        ->and(Arr::pluck($firstTwo, '$builders'))->each->toHaveLength(1)
        ->and($last)->not->toHaveKey('$builders');
});
