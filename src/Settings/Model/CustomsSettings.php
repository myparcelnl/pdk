<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string $countryOfOrigin
 * @property string      $customsCode
 * @property string      $packageContents
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
    public const COUNTRY_OF_ORIGIN = 'countryOfOrigin';
    public const CUSTOMS_CODE      = 'customsCode';
    public const PACKAGE_CONTENTS  = 'packageContents';
    /**
     * Default values.
     */
    public const DEFAULT_PACKAGE_CONTENTS = self::PACKAGE_CONTENTS_COMMERCIAL_GOODS;
    public const DEFAULT_CUSTOMS_CODE     = '0';
    /**
     * Available package contents.
     */
    public const PACKAGE_CONTENTS_COMMERCIAL_GOODS   = 1;
    public const PACKAGE_CONTENTS_COMMERCIAL_SAMPLES = 2;
    public const PACKAGE_CONTENTS_DOCUMENTS          = 3;
    public const PACKAGE_CONTENTS_GIFTS              = 4;
    public const PACKAGE_CONTENTS_RETURN_SHIPMENT    = 5;
    public const PACKAGE_CONTENTS_LIST               = [
        self::PACKAGE_CONTENTS_COMMERCIAL_GOODS   => 'customs_package_contents_commercial_goods',
        self::PACKAGE_CONTENTS_COMMERCIAL_SAMPLES => 'customs_package_contents_commercial_samples',
        self::PACKAGE_CONTENTS_DOCUMENTS          => 'customs_package_contents_documents',
        self::PACKAGE_CONTENTS_GIFTS              => 'customs_package_contents_gifts',
        self::PACKAGE_CONTENTS_RETURN_SHIPMENT    => 'customs_package_contents_return_shipment',
    ];

    protected $attributes = [
        self::COUNTRY_OF_ORIGIN => null,
        self::CUSTOMS_CODE      => self::DEFAULT_CUSTOMS_CODE,
        self::PACKAGE_CONTENTS  => self::DEFAULT_PACKAGE_CONTENTS,
    ];

    protected $casts      = [
        self::COUNTRY_OF_ORIGIN => 'string',
        self::CUSTOMS_CODE      => 'string',
        self::PACKAGE_CONTENTS  => 'string',
    ];
}
