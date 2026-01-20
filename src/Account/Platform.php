<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account;

/**
 * Legacy platform constants - maintained for backward compatibility.
 *
 * @deprecated Use Proposition class constants instead. This class will be removed in a future version.
 * @see \MyParcelNL\Pdk\Account\Proposition
 */
class Platform
{
    public const MYPARCEL_ID       = 1;
    public const MYPARCEL_NAME     = 'myparcel-nederland';
    public const LEGACY_MYPARCEL_NAME = 'myparcel';
    public const SENDMYPARCEL_ID   = 3;
    public const SENDMYPARCEL_NAME = 'myparcel-belgie';
    public const LEGACY_SENDMYPARCEL_NAME = 'belgie';

    public const PLATFORMS_TO_LEGACY_MAP = [
        self::MYPARCEL_NAME   => self::LEGACY_MYPARCEL_NAME,
        self::SENDMYPARCEL_NAME => self::LEGACY_SENDMYPARCEL_NAME
    ];

    public const PLATFORM_ID_TO_NAME_MAP = [
        self::MYPARCEL_ID       => self::MYPARCEL_NAME,
        self::SENDMYPARCEL_ID   => self::SENDMYPARCEL_NAME,
    ];
}
