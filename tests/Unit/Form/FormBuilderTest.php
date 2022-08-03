<?php
/** @noinspection PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form;

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Settings\Model\CustomsSettingsView;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::DEFAULT_CONFIG);
});

it('get customs settings view form', function () {
    $data = [
        'name' => 'defaultForm',
        'label' => 'Default customs form',
        'desc' => 'Lorem',
        'options' => [
            [0, 'Ja'],
            [1, 'Nee'],
        ]
    ];

    $customs =new CustomsSettingsView();
    $customs->fill($data);

    return $form;
});
