<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition;

/**
 * Proposition constants - new terminology replacing Platform.
 *
 * Use these constants for new code. The Platform class constants
 * remain available for backwards compatibility but are deprecated.
 */
class Proposition
{
    public const MYPARCEL_ID               = 1;
    public const MYPARCEL_NAME             = 'myparcel-nederland';
    public const LEGACY_MYPARCEL_NAME      = 'myparcel';

    public const SENDMYPARCEL_ID           = 3;
    public const SENDMYPARCEL_NAME         = 'myparcel-belgie';
    public const LEGACY_SENDMYPARCEL_NAME  = 'belgie';

    public const PROPOSITIONS_TO_LEGACY_MAP = [
        self::MYPARCEL_NAME     => self::LEGACY_MYPARCEL_NAME,
        self::SENDMYPARCEL_NAME => self::LEGACY_SENDMYPARCEL_NAME,
    ];

    public const PROPOSITION_ID_TO_NAME_MAP = [
        self::MYPARCEL_ID     => self::MYPARCEL_NAME,
        self::SENDMYPARCEL_ID => self::SENDMYPARCEL_NAME,
    ];
}

