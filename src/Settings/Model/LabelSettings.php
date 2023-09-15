<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @property null|string $description
 * @property string      $format
 * @property string      $output
 * @property int[]       $position
 * @property bool        $prompt
 */
class LabelSettings extends AbstractSettingsModel
{
    /**
     * Settings category ID.
     */
    final public const ID = 'label';
    /**
     * Settings in this category.
     */
    final public const DESCRIPTION = 'description';
    final public const FORMAT      = 'format';
    final public const OUTPUT      = 'output';
    final public const POSITION    = 'position';
    final public const PROMPT      = 'prompt';
    /**
     * Format options.
     */
    final public const FORMAT_A4 = 'a4';
    final public const FORMAT_A6 = 'a6';
    /**
     * Position options.
     */
    final public const POSITION_1 = 1;
    final public const POSITION_2 = 2;
    final public const POSITION_3 = 3;
    final public const POSITION_4 = 4;
    /**
     * Output options.
     */
    final public const OUTPUT_OPEN     = 'open';
    final public const OUTPUT_DOWNLOAD = 'download';
    /**
     * Default values.
     */
    final public const DEFAULT_FORMAT   = self::FORMAT_A4;
    final public const DEFAULT_POSITION = [self::POSITION_1, self::POSITION_2, self::POSITION_3, self::POSITION_4];
    final public const DEFAULT_OUTPUT   = self::OUTPUT_OPEN;

    protected $attributes = [
        'id' => self::ID,

        self::DESCRIPTION => null,
        self::FORMAT      => self::DEFAULT_FORMAT,
        self::OUTPUT      => self::DEFAULT_OUTPUT,
        self::POSITION    => self::DEFAULT_POSITION,
        self::PROMPT      => false,
    ];

    protected $casts      = [
        self::DESCRIPTION => 'string',
        self::FORMAT      => 'string',
        self::OUTPUT      => 'string',
        self::POSITION    => 'array',
        self::PROMPT      => 'bool',
    ];
}
