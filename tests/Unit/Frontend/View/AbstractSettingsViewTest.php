<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpIllegalPsrClassPathInspection,AutoloadingIssuesInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\PlainElement;
use MyParcelNL\Pdk\Frontend\View\AbstractSettingsView;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('frontend', 'settings');

usesShared(new UsesMockPdkInstance());

final class MockSettingsView extends AbstractSettingsView
{
    private $children;

    private $elements;

    public function __construct($children = null, $elements = null)
    {
        $this->children = $children ? new Collection($children) : null;
        $this->elements = $elements;
    }

    public function render(): string
    {
        return 'test';
    }

    protected function createChildren(): ?Collection
    {
        return $this->children;
    }

    protected function createElements(): ?array
    {
        return $this->elements;
    }

    protected function getSettingsId(): string
    {
        return 'test';
    }
}

it('can render a settings view', function (array $data) {
    $view = new MockSettingsView($data['children'], $data['elements']);

    expect($view->toArray())->toEqual($data['result']);
})->with([
    'base' => function () {
        return [
            'elements' => null,
            'children' => null,
            'result'   => [
                'id'          => 'test',
                'title'       => 'settings_view_test_title',
                'titleSuffix' => null,
                'description' => 'settings_view_test_description',
                'elements'    => null,
                'children'    => null,
            ],
        ];
    },

    'with elements' => function () {
        return [
            'elements' => [
                new InteractiveElement('interactive-test', 'test', ['prop' => 'value']),
                new PlainElement('plain-test', ['prop' => 'value']),
            ],
            'children' => null,
            'result'   => [
                'id'          => 'test',
                'title'       => 'settings_view_test_title',
                'titleSuffix' => null,
                'description' => 'settings_view_test_description',
                'elements'    => [
                    [
                        'name'        => 'interactive-test',
                        '$component'  => 'test',
                        'prop'        => 'value',
                        'label'       => 'settings_test_interactive-test',
                        'description' => 'settings_test_interactive-test_description',
                    ],
                    [
                        '$component' => 'plain-test',
                        '$wrapper'   => false,
                        'prop'       => 'value',
                    ],
                ],
                'children'    => null,
            ],
        ];
    },

    'with children' => function () {
        return [
            'elements' => null,
            'children' => [
                [
                    'id'          => 'test',
                    'title'       => 'test.view.test.title',
                    'description' => 'test.view.test.description',
                    'elements'    => [],
                    'children'    => [],
                ],
            ],
            'result'   => [
                'id'          => 'test',
                'title'       => 'settings_view_test_title',
                'titleSuffix' => null,
                'description' => 'settings_view_test_description',
                'elements'    => null,
                'children'    => [
                    [
                        'id'          => 'test',
                        'title'       => 'test.view.test.title',
                        'description' => 'test.view.test.description',
                        'elements'    => [],
                        'children'    => [],
                    ],
                ],
            ],
        ];
    },
]);
