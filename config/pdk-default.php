<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use function DI\env;
use function DI\factory;
use function DI\value;

/**
 * Default config values.
 */
return [
    /**
     * Path to the root directory of the pdk.
     */
    'rootDir'                  => value(__DIR__ . '/../'),

    /**
     * Directories to load config files from.
     */
    'configDirs'               => value([
        __DIR__ . '/../config',
    ]),

    /**
     * Url to the API.
     */
    'apiUrl'                   => env('PDK_API_URL', 'https://api.myparcel.nl'),

    /**
     * CDN URL to use for frontend dependencies.
     */
    'baseCdnUrl'               => value('https://cdnjs.cloudflare.com/ajax/libs/:name/:version/:filename'),

    /**
     * The default date format to use for date and time functions.
     */
    'defaultDateFormat'        => value('Y-m-d H:i:s'),

    /**
     * Short date format.
     */
    'defaultDateFormatShort'   => value('Y-m-d'),

    /**
     * Supported date formats.
     */
    'dateFormats'              => factory(function () {
        return [
            'Y-m-d\TH:i:s.uP',
            'Y-m-d\TH:i:sP',
            'Y-m-d H:i:s.u',
            Pdk::get('defaultDateFormat'),
            Pdk::get('defaultDateFormatShort'),
        ];
    }),

    /**
     * The default time zone to use for date and time functions.
     */
    'defaultTimeZone'          => value('Europe/Amsterdam'),

    /**
     * Package types, ordered by size from largest to smallest.
     */
    'packageTypesBySize'       => value([
        DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
        DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
    ]),

    /**
     * Allowed positions for the delivery options in the checkout.
     */
    'deliveryOptionsPositions' => value([]),

    /**
     * All carriers, merged with the root carrier definitions.
     */

    'allCarriers' => factory(function (): CarrierCollection {
        $platformCarriers = new Collection(Platform::get('carriers'));
        $carriers         = new Collection(Config::get('carriers'));

        $result = $carriers
            ->map(function (array $carrier) use ($platformCarriers): array {
                $carrierDefinition = $platformCarriers->firstWhere('name', $carrier['name']) ?? [];

                return array_replace($carrier, $carrierDefinition);
            });

        return new CarrierCollection($result);
    }),

    /**
     * Carriers filtered by those allowed in the current platform.
     */

    'carriers' => factory(function (): CarrierCollection {
        /** @var CarrierCollection $allCarriers */
        $allCarriers      = Pdk::get('allCarriers');
        $platformCarriers = new Collection(Platform::get('carriers'));

        return $allCarriers
            ->whereIn('name', $platformCarriers->pluck('name'))
            ->values();
    }),
];
