<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form;

/**
 * @todo remove when forms are converted to new format
 */
class SettingsDivider extends PlainElement
{
    final public const LEVEL_1       = 1;
    final public const LEVEL_2       = 2;
    final public const LEVEL_3       = 3;
    final public const LEVEL_4       = 4;
    final public const LEVEL_5       = 5;
    final public const LEVEL_6       = 6;
    final public const DEFAULT_LEVEL = self::LEVEL_2;

    /**
     * @param  null|int $level
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
