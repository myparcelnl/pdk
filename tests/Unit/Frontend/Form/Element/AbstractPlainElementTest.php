<?php
/** @noinspection ALL */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

class Element extends AbstractPlainElement
{
    protected function getComponent(): string
    {
        return 'test';
    }
}

it('adds props', function () {
    $element = new Element();

    $element->withProp('test', 1)
        ->withProps(['test2' => 2, 'test3' => 3]);

    $created = $element->make()
        ->toArray();

    expect($created)
        ->toEqual([
            '$component' => 'test',
            '$wrapper'   => false,
            'test'       => 1,
            'test2'      => 2,
            'test3'      => 3,
        ]);
});

it('adds attributes', function () {
    $element = new Element();

    $element->withAttribute('attr', 4)
        ->withAttributes(['attr2' => 5, 'attr3' => 6]);

    $created = $element->make()
        ->toArray();

    expect($created)
        ->toEqual([
            '$component'  => 'test',
            '$wrapper'    => false,
            '$attributes' => [
                'attr'  => 4,
                'attr2' => 5,
                'attr3' => 6,
            ],
        ]);
});

it('sets and gets name', function () {
    $element = new Element();

    expect($element->getName())->toBeNull();

    $element->withName('test');

    expect($element->getName())->toBe('test');

    $created = $element->make()
        ->toArray();

    expect($created)
        ->toEqual([
            'name'       => 'test',
            '$component' => 'test',
            '$wrapper'   => false,
        ]);
});

it('sets name', function () {
    $element = new Element();

    $element->withName('test');

    $created = $element->make()
        ->toArray();

    expect($created)
        ->toEqual([
            'name'       => 'test',
            '$component' => 'test',
            '$wrapper'   => false,
        ]);
});

it('adds visibleWhen hook', function () {
    $element = new Element('test');

    $element->visibleWhen('test', 'hello');

    $created = $element->make()
        ->toArray();

    expect($created)
        ->toHaveKey('$builders')
        ->and($created['$builders'][0])
        ->toHaveKey('$visibleWhen');
});;
