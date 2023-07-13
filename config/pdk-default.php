<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
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
     * Url to the API.
     */
    'apiUrl'                   => env('PDK_API_URL', 'https://api.myparcel.nl'),

    /**
     * CDN URL to use for frontend dependencies.
     */
    'baseCdnUrl'               => value('https://cdnjs.cloudflare.com/ajax/libs/:name/:version/:filename'),

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
];
