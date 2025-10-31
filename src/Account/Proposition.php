<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account;

/**
 * This replaces the legacy Platform class.
 */
class Proposition
{

    public const MYPARCEL_ID       = 1;
    public const MYPARCEL_NAME     = 'myparcel';

    /**
     * Note: Platform constants use 2, but platformId 3 is also supported during transition
     */
    public const SENDMYPARCEL_ID   = 2;
    public const SENDMYPARCEL_NAME = 'belgie';
}
