<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @property null|string $description
 * @property string      $format
 * @property string      $output
 * @property int         $position
 * @property bool        $prompt
 */
class LabelSettings extends AbstractSettingsModel
{
    /**
     * Settings category ID.
     */
    public const ID = 'label';
    /**
     * Settings in this category.
     */
    public const DESCRIPTION = 'description';
    public const FORMAT      = 'format';
    public const OUTPUT      = 'output';
    public const POSITION    = 'position';
    public const PROMPT      = 'prompt';
    /**
     * Format options.
     */
    public const FORMAT_A4 = 'a4';
    public const FORMAT_A6 = 'a6';
    /**
     * Position options.
     */
    public const POSITION_1 = 1;
    public const POSITION_2 = 2;
    public const POSITION_3 = 3;
    public const POSITION_4 = 4;
    /**
     * Output options.
     */
    public const OUTPUT_OPEN     = 'open';
    public const OUTPUT_DOWNLOAD = 'download';
    /**
     * Default values.
     */
    public const DEFAULT_FORMAT   = self::FORMAT_A4;
    public const DEFAULT_POSITION = self::POSITION_1;
    public const DEFAULT_OUTPUT   = self::OUTPUT_OPEN;

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
        self::POSITION    => 'int',
        self::PROMPT      => 'bool',
    ];
}
