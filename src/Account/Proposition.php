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
     * Note: Became 3 after INT-1084 PR was merged (previously 2)
     */
    public const SENDMYPARCEL_ID   = 3;
    public const SENDMYPARCEL_NAME = 'belgie';
}
