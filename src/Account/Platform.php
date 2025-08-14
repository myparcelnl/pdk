<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account;

class Platform
{
    public const MYPARCEL_ID       = 1;
    public const MYPARCEL_NAME     = 'myparcel-nederland';
    public const SENDMYPARCEL_ID   = 3;
    public const SENDMYPARCEL_NAME = 'myparcel-belgie';

    public const PLATFORMS_TO_LEGACY_MAP = [
        self::FLESPAKKET_NAME => self::FLESPAKKET_NAME,
        self::MYPARCEL_NAME   => 'myparcel',
        self::SENDMYPARCEL_NAME => 'sendmyparcel'
    ];

    public const PLATFORM_ID_TO_NAME_MAP = [
        self::FLESPAKKET_ID     => self::FLESPAKKET_NAME,
        self::MYPARCEL_ID       => self::MYPARCEL_NAME,
        self::SENDMYPARCEL_ID   => self::SENDMYPARCEL_NAME,
    ];
}
