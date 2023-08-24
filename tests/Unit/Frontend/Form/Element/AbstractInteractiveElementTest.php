<?php
/** @noinspection AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

use MyParcelNL\Pdk\Frontend\Form\Builder\FormAfterUpdateBuilder;

final class Input extends AbstractInteractiveElement
{
    protected function getComponent(): string
    {
        return 'test';
    }
}

it('adds afterUpdate hook', function () {
    $element = new Input('test');

    $element->afterUpdate(function (FormAfterUpdateBuilder $builder) {
        $builder->setValue('test');
    });

    $created = $element->make()
        ->toArray();

    expect($created)
        ->toHaveKey('$builders')
        ->and($created['$builders'][0])
        ->toHaveKey('$afterUpdate');
});

it('adds readOnlyWhen hook', function () {
    $element = new Input('test');

    $element->readOnlyWhen('test', 'hello');

    $created = $element->make()
        ->toArray();

    expect($created)
        ->toHaveKey('$builders')
        ->and($created['$builders'][0])
        ->toHaveKey('$readOnlyWhen');
});
