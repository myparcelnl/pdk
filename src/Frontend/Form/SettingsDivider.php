<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

class SettingsDivider extends PlainElement
{
    private const DEFAULT_LEVEL = 2;

    /**
     * @param  string   $translation
     * @param  null|int $level
     * @param  array    $props
     */
    public function __construct(string $translation, ?int $level = null, array $props = [])
    {
        parent::__construct(
            Components::SETTINGS_DIVIDER,
            $props + [
                'content' => "{$translation}_description",
                'heading' => "{$translation}_title",
                'level'   => $level ?? self::DEFAULT_LEVEL,
            ]
        );
    }
}
