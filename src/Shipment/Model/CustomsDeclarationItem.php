<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Settings;
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
        'classification' => null,
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
        $classification = $product->settings->customsCode ?? Settings::get('customs.customsCode');
        $country        = $product->settings->countryOfOrigin ?? Settings::get('customs.countryOfOrigin');

        return new static(
            array_merge(
                self::DEFAULTS,
                Utils::filterNull([
                    'classification' => $classification,
                    'country'        => $country,
                    'description'    => $product->name,
                    'weight'         => $product->weight,
                    'itemValue'      => [
                        'currency' => $product->price->currency,
                        'amount'   => $product->price->amount,
                    ],
                ]),
                $additionalFields
            )
        );
    }
}
