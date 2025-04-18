<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

/**
 * Document deprecated properties for address
 */
trait DeprecatedAddressProperties
{
    /**
     * @deprecated   split into $street and $number
     * @noinspection PhpUnused
     * @var string
     */
    public $address1;

    /**
     * @deprecated   use $streetAdditionalInfo
     * @noinspection PhpUnused
     * @var string
     */
    public $address2;

    /**
     * @deprecated   no replacement
     * @noinspection PhpUnused
     */
    public $area;
}
