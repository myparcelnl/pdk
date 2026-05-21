<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
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
    'rootDir'                => value(__DIR__ . '/../'),

    /**
     * Directories to load config files from.
     */
    'configDirs'             => value([
        __DIR__ . '/../config',
    ]),

    /**
     * Url to the API.
     */
    //@deprecated, use the apiUrl from the OpenAPI spec instead.
    'apiUrl'                 => env('PDK_API_URL', 'https://api.myparcel.nl'),
    'printingApiUrl'         => env('PDK_PRINTING_API_URL', 'https://printing.api.myparcel.nl'),
    'addressesServiceUrl'    => env('PDK_ADDRESSES_SERVICE_URL', 'https://address.api.myparcel.nl'),

    /**
     * Security settings for the proxy
     */
    'allowedProxyHosts'      => env('PDK_ALLOWED_PROXY_HOSTS', ['self']),
    'allowedProxyOrigins'    => env('PDK_ALLOWED_PROXY_ORIGINS', ['self']),

    /**
     * CDN URL to use for frontend dependencies.
     */
    'baseCdnUrl'             => value('https://cdn.jsdelivr.net/npm/:name@:version/:filename'),

    /**
     * The default date format to use for date and time functions.
     */
    'defaultDateFormat'      => value('Y-m-d H:i:s'),

    /**
     * Short date format.
     */
    'defaultDateFormatShort' => value('Y-m-d'),

    /**
     * Supported date formats.
     * @todo get/set from proposition config (internationalization.dateFormats)
     */
    'dateFormats'            => factory(function () {
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
     * @todo get/set from proposition config (internationalization.localTimeZone)
     */
    'defaultTimeZone'        => value('Europe/Amsterdam'),

    /**
     * Bulk order actions.
     *
     * @example Pdk::get('bulkActions') // gets the bulk actions for the current order mode.
     */

    'allBulkActions' => value([
        'Shipments' => [
            'action_print',
            'action_export_print',
            'action_export',
            'action_edit',
        ],
        'OrderV1'   => [
            'action_edit',
            'action_export',
        ],
        'OrderV2'   => [
            'action_print',
            'action_export_print',
            'action_export',
            'action_edit',
        ],
    ]),

    'bulkActions'              => factory(static function (): array {
        $all = PdkFacade::get('allBulkActions');
        $key = [
            AccountFeaturesServiceInterface::ORDER_MODE_V1 => 'OrderV1',
            AccountFeaturesServiceInterface::ORDER_MODE_V2 => 'OrderV2',
        ][AccountSettings::getEffectiveOrderMode()] ?? 'Shipments';

        return Arr::get($all, $key, []);
    }),

    /**
     * Allowed positions for the delivery options in the checkout.
     */
    'deliveryOptionsPositions' => value([]),

    /**
     * Language to default to when no language is set.
     * @todo refactor to use the proposition config instead of the hardcoded value.
     */
    'defaultLanguage'                  => value('en'),

    /**
     * Languages present in the translations directory after the build process.
     * @todo refactor to use the proposition config instead of the hardcoded array.
     */
    'availableLanguages'               => value(['en', 'nl', 'fr', 'de', 'it']),

    /**
     * The prefix to use for delivery options translations.
     */
    'translationPrefixDeliveryOptions' => value('delivery_options_'),
];
