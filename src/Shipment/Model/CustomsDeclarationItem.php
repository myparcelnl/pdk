<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;

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
    protected $attributes = [
        'amount'         => 1,
        'classification' => null,
        'country'        => null,
        'description'    => null,
        /**
         * This value must always be at least 100 cents (1 EUR).
         */
        'itemValue'      => Currency::class,
        'weight'         => 0,
    ];

    protected $casts      = [
        'amount'         => 'int',
        'classification' => 'string',
        'country'        => 'string',
        'description'    => 'string',
        'itemValue'      => Currency::class,
        'weight'         => 'int',
    ];

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrderLine $orderLine
     *
     * @return static
     */
    public static function fromOrderLine(PdkOrderLine $orderLine): self
    {
        $triStateService = Pdk::get(TriStateServiceInterface::class);
        $productSettings = $orderLine->product->settings;

        $classification = $triStateService->resolveString(
            $productSettings->customsCode,
            Settings::get(CustomsSettings::CUSTOMS_CODE, CustomsSettings::ID)
        );

        $country = $triStateService->resolveString(
            $productSettings->countryOfOrigin,
            Settings::get(CustomsSettings::COUNTRY_OF_ORIGIN, CustomsSettings::ID),
            Platform::get('localCountry')
        );

        return new static(
            Utils::filterNull([
                'amount'         => $orderLine->quantity,
                'classification' => $classification,
                'country'        => $country,
                'description'    => $orderLine->product->name,
                'weight'         => $orderLine->product->weight,
                'itemValue'      => [
                    'amount'   => $orderLine->product->price->amount,
                    'currency' => $orderLine->product->price->currency,
                ],
            ])
        );
    }
}
