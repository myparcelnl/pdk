<?php
/** @noinspection PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Form\Model\Input\CheckboxInput;
use MyParcelNL\Pdk\Form\Model\Input\HiddenInput;
use MyParcelNL\Pdk\Form\Model\Input\RadioButtonInput;
use MyParcelNL\Pdk\Form\Model\Input\Select\CountrySelectInput;
use MyParcelNL\Pdk\Form\Model\Input\Select\DropOffDaySelectInput;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Form\Model\Input\ToggleInput;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::create());
});

it('creates input', function (string $class, array $input, array $output) {
    $instance = new $class($input);

    expect($instance)
        ->toBeInstanceOf($class)
        ->and(Arr::dot($instance->toArray()))
        ->toEqual($output);
})->with([
    'TextInput'             => [
        'class'  => TextInput::class,
        'input'  => [
            'name'        => 'bloemkool',
            'label'       => 'Bloemkool text',
            'description' => 'Dit is een bloemkool',
        ],
        'output' => [
            'name'        => 'bloemkool',
            'label'       => 'Bloemkool text',
            'description' => 'Dit is een bloemkool',
            'type'        => 'TextInput',
        ],
    ],
    'SelectInput'           => [
        'class'  => SelectInput::class,
        'input'  => [
            'name'        => 'selector',
            'label'       => 'selector text',
            'description' => 'Dit is een selector',
        ],
        'output' => [
            'name'        => 'selector',
            'label'       => 'selector text',
            'description' => 'Dit is een selector',
            'options'     => [],
            'type'        => 'SelectInput',
        ],
    ],
    'ToggleInput'           => [
        'class'  => ToggleInput::class,
        'input'  => [
            'name'        => 'appelboom',
            'label'       => 'Appelboom text',
            'description' => 'Dit is een appelboom',
        ],
        'output' => [
            'name'           => 'appelboom',
            'label'          => 'Appelboom text',
            'description'    => 'Dit is een appelboom',
            'isBool'         => true,
            'values.0.id'    => 'on',
            'values.0.value' => 1,
            'values.0.label' => 'Yes',
            'values.1.id'    => 'off',
            'values.1.value' => 0,
            'values.1.label' => 'No',
            'type'           => 'ToggleInput',
        ],
    ],
    'CheckboxInput'         => [
        'class'  => CheckboxInput::class,
        'input'  => [
            'name'        => 'bloemkool',
            'label'       => 'Bloemkool text',
            'description' => 'Dit is een bloemkool',
        ],
        'output' => [
            'name'        => 'bloemkool',
            'label'       => 'Bloemkool text',
            'description' => 'Dit is een bloemkool',
            'type'        => 'CheckboxInput',
        ],
    ],
    'RadioButtonInput'      => [
        'class'  => RadioButtonInput::class,
        'input'  => [
            'name'        => 'r4di0',
            'label'       => 'R4di0 text',
            'description' => 'Dit is een r4di0',
            'multiple'    => false,
            'options'     => [
                [
                    'id'   => 1,
                    'name' => 'Spareribs',
                ],
            ],
        ],
        'output' => [
            'name'           => 'r4di0',
            'label'          => 'R4di0 text',
            'description'    => 'Dit is een r4di0',
            'multiple'       => false,
            'options.0.id'   => 1,
            'options.0.name' => 'Spareribs',
            'type'           => 'RadioButtonInput',
        ],
    ],
    'HiddenInput'           => [
        'class'  => HiddenInput::class,
        'input'  => [
            'name'        => 'bloemkool',
            'label'       => 'Bloemkool text',
            'description' => 'Dit is een bloemkool',
        ],
        'output' => [
            'name'        => 'bloemkool',
            'label'       => 'Bloemkool text',
            'description' => 'Dit is een bloemkool',
            'type'        => 'HiddenInput',
        ],
    ],
    'CountrySelectInput'    => [
        'class'  => CountrySelectInput::class,
        'input'  => [
            'name'        => 'bloemkool',
            'label'       => 'Bloemkool text',
            'description' => 'Dit is een bloemkool',
        ],
        'output' => [
            'name'        => 'bloemkool',
            'label'       => 'Bloemkool text',
            'description' => 'Dit is een bloemkool',
            'options'     => [],
            'type'        => 'CountrySelectInput',
        ],
    ],
    'DropOffDaySelectInput' => [
        'class'  => DropOffDaySelectInput::class,
        'input'  => [
            'name'        => 'bloemkool',
            'label'       => 'Bloemkool text',
            'description' => 'Dit is een bloemkool',
        ],
        'output' => [
            'name'        => 'bloemkool',
            'label'       => 'Bloemkool text',
            'description' => 'Dit is een bloemkool',
            'multiple'    => false,
            'values'      => [],
            'type'        => 'DropOffDaySelectInput',
        ],
    ],
]);
