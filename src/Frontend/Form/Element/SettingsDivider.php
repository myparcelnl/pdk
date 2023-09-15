<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

use MyParcelNL\Pdk\Frontend\Form\Components;

final class SettingsDivider extends AbstractPlainElement
{
    public const LEVEL_1       = 1;
    public const LEVEL_2       = 2;
    public const LEVEL_3       = 3;
    public const LEVEL_4       = 4;
    public const LEVEL_5       = 5;
    public const LEVEL_6       = 6;
    public const DEFAULT_LEVEL = self::LEVEL_2;

    /**
     * @param  null|int $level
     */
    public function __construct(string $translation, ?int $level = null, array $props = [])
    {
        $this->withProps(
            array_replace([
                'level'   => $level ?? self::DEFAULT_LEVEL,
                'content' => "{$translation}_description",
                'heading' => "{$translation}_title",
            ], $props)
        );
    }

    protected function getComponent(): string
    {
        return Components::SETTINGS_DIVIDER;
    }
}
