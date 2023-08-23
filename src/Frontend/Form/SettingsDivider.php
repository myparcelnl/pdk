<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

/**
 * @todo remove when forms are converted to new format
 */
class SettingsDivider extends PlainElement
{
    public const LEVEL_1       = 1;
    public const LEVEL_2       = 2;
    public const LEVEL_3       = 3;
    public const LEVEL_4       = 4;
    public const LEVEL_5       = 5;
    public const LEVEL_6       = 6;
    public const DEFAULT_LEVEL = self::LEVEL_2;

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
