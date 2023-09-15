<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @property null|string $countryOfOrigin
 * @property string      $customsCode
 * @property string      $packageContents
 */
class CustomsSettings extends AbstractSettingsModel
{
    /**
     * Settings category ID.
     */
    final public const ID = 'customs';
    /**
     * Settings in this category.
     */
    final public const COUNTRY_OF_ORIGIN = 'countryOfOrigin';
    final public const CUSTOMS_CODE      = 'customsCode';
    final public const PACKAGE_CONTENTS  = 'packageContents';
    /**
     * Default values.
     */
    final public const DEFAULT_PACKAGE_CONTENTS = self::PACKAGE_CONTENTS_COMMERCIAL_GOODS;
    final public const DEFAULT_CUSTOMS_CODE     = '0';
    /**
     * Available package contents.
     */
    final public const PACKAGE_CONTENTS_COMMERCIAL_GOODS   = 1;
    final public const PACKAGE_CONTENTS_COMMERCIAL_SAMPLES = 2;
    final public const PACKAGE_CONTENTS_DOCUMENTS          = 3;
    final public const PACKAGE_CONTENTS_GIFTS              = 4;
    final public const PACKAGE_CONTENTS_RETURN_SHIPMENT    = 5;
    final public const PACKAGE_CONTENTS_LIST               = [
        self::PACKAGE_CONTENTS_COMMERCIAL_GOODS   => 'customs_package_contents_commercial_goods',
        self::PACKAGE_CONTENTS_COMMERCIAL_SAMPLES => 'customs_package_contents_commercial_samples',
        self::PACKAGE_CONTENTS_DOCUMENTS          => 'customs_package_contents_documents',
        self::PACKAGE_CONTENTS_GIFTS              => 'customs_package_contents_gifts',
        self::PACKAGE_CONTENTS_RETURN_SHIPMENT    => 'customs_package_contents_return_shipment',
    ];

    protected $attributes = [
        'id' => self::ID,

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
