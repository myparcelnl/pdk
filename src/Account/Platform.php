<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account;

/**
 * Legacy platform constants - maintained for backward compatibility
 *
 * @deprecated Use Proposition class constants instead. This class will be removed in a future version.
 */
class Platform
{
    /**
     * @deprecated Will be removed in future version. Flespakket is discontinued.
     */
    public const FLESPAKKET_ID     = 3;
    /**
     * @deprecated Will be removed in future version. Flespakket is discontinued.
     */
    public const FLESPAKKET_NAME   = 'flespakket';
    public const MYPARCEL_ID       = 1;
    public const MYPARCEL_NAME     = 'myparcel';
    public const SENDMYPARCEL_ID   = 2;
    public const SENDMYPARCEL_NAME = 'belgie';
}
