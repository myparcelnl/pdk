<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Pdk;
use function DI\env;
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
     * Mode to use for the PDK. Defaults to production. Set to debug to show debug messages and stack traces in exceptions.
     */
    'mode'                     => env('PDK_MODE', Pdk::MODE_PRODUCTION),

    /**
     * Url to the API.
     */
    'apiUrl'                   => env('PDK_API_URL', 'https://api.myparcel.nl'),

    /**
     * CDN URL to use for frontend dependencies.
     */
    'baseCdnUrl'               => value('https://cdnjs.cloudflare.com/ajax/libs/:name/:version/:filename'),

    /**
     * The minimum PHP version required to run the app.
     */
    'minimumPhpVersion'        => value('7.1'),

    /**
     * The version of the delivery options in the checkout.
     *
     * @see https://github.com/myparcelnl/delivery-options/releases
     */
    'deliveryOptionsVersion'   => value('5.7.1'),

    /**
     * The default time zone to use for date and time functions.
     */
    'defaultTimeZone'          => value('Europe/Amsterdam'),

    /**
     * Carriers that can be used and shown. Only use carriers that we tested and have a schema for, at the moment
     */
    'allowedCarriers'          => value([
        'dhleuroplus',
        'dhlforyou',
        'dhlparcelconnect',
        'postnl',
        // todo: bpost
        // todo: dpd
    ]),

    /**
     * Allowed positions for the delivery options in the checkout.
     */
    'deliveryOptionsPositions' => value([]),
];
