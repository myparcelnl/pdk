<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;

/**
 * @property int                                 $amount
 * @property null|string                         $classification
 * @property null|string                         $country
 * @property null|string                         $description
 * @property \MyParcelNL\Pdk\Base\Model\Currency $itemValue
 * @property int                                 $weight
 */
class CustomsDeclarationItem extends Model
{
    public const  DEFAULT_CLASSIFICATION = '0000';
    private const DEFAULTS               = [
        'amount'         => 1,
        'classification' => self::DEFAULT_CLASSIFICATION,
        'country'        => null,
        'description'    => null,
        'itemValue'      => Currency::class,
        'weight'         => 0,
    ];

    protected $attributes = self::DEFAULTS;

    protected $casts      = [
        'amount'         => 'int',
        'classification' => 'string',
        'country'        => 'string',
        'description'    => 'string',
        'itemValue'      => Currency::class,
        'weight'         => 'int',
    ];

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkProduct $product
     * @param  array                                   $additionalFields
     *
     * @return static
     */
    public static function fromProduct(PdkProduct $product, array $additionalFields = []): self
    {
        return new static(
            array_merge(
                self::DEFAULTS,
                Utils::filterNull([
                    'classification' => $product->settings->customsCode,
                    'country'        => $product->settings->countryOfOrigin,
                    'description'    => $product->name,
                    'weight'         => $product->weight,
                ]),
                $additionalFields
            )
        );
    }
}
