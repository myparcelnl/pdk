<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

use MyParcelNL\Pdk\Frontend\Form\Components;

it('creates a settings divider', function (int $level): void {
    $class = new SettingsDivider('my_setting_group', $level);

    $result = $class->make()
        ->toArray();

    expect($result)->toEqual([
        '$component' => Components::SETTINGS_DIVIDER,
        '$wrapper'   => false,
        'level'      => $level,
        'content'    => 'my_setting_group_description',
        'heading'    => 'my_setting_group_title',
    ]);
})->with([
    SettingsDivider::LEVEL_1,
    SettingsDivider::LEVEL_2,
    SettingsDivider::LEVEL_3,
    SettingsDivider::LEVEL_4,
    SettingsDivider::LEVEL_5,
    SettingsDivider::LEVEL_6,
]);
