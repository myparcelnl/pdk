<?php
/** @noinspection AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\TextInput;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

final class MockView extends NewAbstractSettingsView
{
    protected function addElements(): void
    {
        $this->formBuilder->add(
            new TextInput('test1'),
            new TextInput('test2'),
            new TextInput('test3')
        );
    }

    protected function getPrefix(): string
    {
        return 'categoryId';
    }
}

it('does not render settings that are disabled', function () {
    $reset = mockPdkProperty('disabledSettings', [
        'categoryId' => [
            'test1',
            'test3',
        ],
    ]);

    /** @var \MyParcelNL\Pdk\Frontend\View\LabelSettingsView $view */
    $view = new MockView();

    $elements = array_map(static function (ElementBuilderInterface $builder) {
        return $builder->getName();
    }, $view->all());

    expect($elements)->toEqual(['test2']);

    $reset();
});
