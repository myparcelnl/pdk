<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string $defaultCountryOfOrigin
 * @property string      $defaultCustomsCode
 * @property string      $defaultPackageContents
 */
class CustomsSettings extends Model
{
    /**
     * Settings category ID.
     */
    public const ID = 'customs';
    /**
     * Settings in this category.
     */
    public const DEFAULT_COUNTRY_OF_ORIGIN = 'defaultCountryOfOrigin';
    public const DEFAULT_CUSTOMS_CODE      = 'defaultCustomsCode';
    public const DEFAULT_PACKAGE_CONTENTS  = 'defaultPackageContents';
    /**
     * Package contents
     */
    public const PACKAGE_CONTENTS_COMMERCIAL_GOODS   = 1;
    public const PACKAGE_CONTENTS_COMMERCIAL_SAMPLES = 2;
    public const PACKAGE_CONTENTS_DOCUMENTS          = 3;
    public const PACKAGE_CONTENTS_GIFTS              = 4;
    public const PACKAGE_CONTENTS_RETURN_SHIPMENT    = 5;
    public const PACKAGE_CONTENTS_LIST               = [
        self::PACKAGE_CONTENTS_COMMERCIAL_GOODS   => 'Commercial goods',
        self::PACKAGE_CONTENTS_COMMERCIAL_SAMPLES => 'Commercial samples',
        self::PACKAGE_CONTENTS_DOCUMENTS          => 'Documents',
        self::PACKAGE_CONTENTS_GIFTS              => 'Gifts',
        self::PACKAGE_CONTENTS_RETURN_SHIPMENT    => 'Return shipment',
    ];

    protected $attributes = [
        self::DEFAULT_COUNTRY_OF_ORIGIN => null,
        self::DEFAULT_CUSTOMS_CODE      => '0',
        self::DEFAULT_PACKAGE_CONTENTS  => self::PACKAGE_CONTENTS_COMMERCIAL_GOODS,
    ];

    protected $casts      = [
        self::DEFAULT_COUNTRY_OF_ORIGIN => 'string',
        self::DEFAULT_CUSTOMS_CODE      => 'string',
        self::DEFAULT_PACKAGE_CONTENTS  => 'string',
    ];
}
