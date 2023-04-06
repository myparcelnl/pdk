<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

class SettingsDivider extends PlainElement
{
    public function __construct(string $translation, int $level = 2)
    {
        parent::__construct(Components::SETTINGS_DIVIDER, [
            'content' => "{$translation}_description",
            'heading' => "{$translation}_title",
            'level'   => $level,
        ]);
    }
}
