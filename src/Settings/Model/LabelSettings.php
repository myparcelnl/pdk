<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string $defaultPosition
 * @property null|string $labelDescription
 * @property null|string $labelOpenDownload
 * @property null|string $labelSize
 * @property bool        $promptPosition
 */
class LabelSettings extends Model
{
    public const DEFAULT_POSITION    = 'defaultPosition';
    public const LABEL_DESCRIPTION   = 'labelDescription';
    public const LABEL_OPEN_DOWNLOAD = 'labelOpenDownload';
    public const LABEL_SIZE          = 'labelSize';
    public const PROMPT_POSITION     = 'promptPosition';

    protected $attributes = [
        self::DEFAULT_POSITION    => null,
        self::LABEL_DESCRIPTION   => null,
        self::LABEL_OPEN_DOWNLOAD => null,
        self::LABEL_SIZE          => null,
        self::PROMPT_POSITION     => false,
    ];

    protected $casts      = [
        self::DEFAULT_POSITION    => 'string',
        self::LABEL_DESCRIPTION   => 'string',
        self::LABEL_OPEN_DOWNLOAD => 'string',
        self::LABEL_SIZE          => 'string',
        self::PROMPT_POSITION     => 'bool',
    ];
}
