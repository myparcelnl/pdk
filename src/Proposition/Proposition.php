<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition;

use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\AccountDefsPlatformName;

/**
 * Proposition constants - terminology replacing the legacy Platform class.
 *
 * - MYPARCEL_NAME / SENDMYPARCEL_NAME: the machine-readable proposition key (e.g. used in file paths).
 * - PLATFORM_NAME_*: the platform identifier as defined by AccountDefsPlatformName in the SDK,
 *   used in delivery options configuration and API communication.
 */
class Proposition
{
    public const MYPARCEL_ID    = 1;
    public const MYPARCEL_NAME  = 'myparcel-nederland';

    public const SENDMYPARCEL_ID    = 3;
    public const SENDMYPARCEL_NAME  = 'myparcel-belgie';

    /** Platform name as defined by AccountDefsPlatformName::MYPARCEL. */
    public const PLATFORM_NAME_MYPARCEL     = AccountDefsPlatformName::MYPARCEL;

    /** Platform name as defined by AccountDefsPlatformName::BELGIE. */
    public const PLATFORM_NAME_SENDMYPARCEL = AccountDefsPlatformName::BELGIE;

    /**
     * Maps a proposition key (e.g. 'myparcel-nederland') to its AccountDefsPlatformName value.
     */
    public const PROPOSITION_KEY_TO_PLATFORM_NAME_MAP = [
        self::MYPARCEL_NAME     => self::PLATFORM_NAME_MYPARCEL,
        self::SENDMYPARCEL_NAME => self::PLATFORM_NAME_SENDMYPARCEL,
    ];

    /**
     * Maps a proposition ID to its proposition key.
     */
    public const PROPOSITION_ID_TO_NAME_MAP = [
        self::MYPARCEL_ID     => self::MYPARCEL_NAME,
        self::SENDMYPARCEL_ID => self::SENDMYPARCEL_NAME,
    ];
}
