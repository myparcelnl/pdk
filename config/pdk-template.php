<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Api\Contract\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Api\Contract\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookServiceInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\get;
use function DI\value;

/**
 * Template for the config/pdk.php file.
 */
return [
    /**
     * Information about the app that is using the PDK.
     */
    'appInfo'         => value([
        'name'    => null,
        'title'   => null,
        'path'    => null,
        'url'     => null,
        'version' => null,
    ]),

    /**
     * User agent to pass to requests.
     */
    'userAgent'       => value([]),

    /**
     * Default settings.
     */
    'defaultSettings' => value([]),

    #####
    # Repositories
    #
    # These repositories are used to store, retrieve and convert data in your app. You can extend the abstract classes
    # and implement your own logic.
    #####

    /**
     * Handles account data being saved and retrieved in your app.
     *
     * @see \MyParcelNL\Pdk\Account\Repository\AbstractAccountRepository
     */

    PdkAccountRepositoryInterface::class => autowire(),

    /**
     * Handles conversion of your app's order data to PDK order data.
     *
     * @see \MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderRepository
     */

    PdkOrderRepositoryInterface::class => autowire(),

    /**
     * Handles webhook data being saved and retrieved in your app.
     *
     * @note Required for plugin settings.
     * @see  \MyParcelNL\Pdk\App\Webhook\Repository\AbstractPdkWebhooksRepository
     */

    PdkWebhooksRepositoryInterface::class => autowire(),

    /**
     * Handles settings being saved and retrieved in your app.
     *
     * @note Required for plugin settings.
     * @see  \MyParcelNL\Pdk\Settings\Repository\AbstractPdkSettingsRepository
     */

    PdkSettingsRepositoryInterface::class => autowire(),

    /**
     * Handles conversion of your app's cart data to PDK cart data.
     *
     * @note Required for using the checkout.
     * @see  \MyParcelNL\Pdk\App\Cart\Repository\AbstractPdkCartRepository
     */

    PdkCartRepositoryInterface::class => autowire(),

    /**
     * Handles conversion of your app's shipping method data to PDK shipping method data.
     *
     * @note Required for using the checkout.
     * @see  \MyParcelNL\Pdk\App\ShippingMethod\Repository\AbstractPdkShippingMethodRepository
     */

    PdkShippingMethodRepositoryInterface::class => autowire(),

    #####
    # Required services
    #
    # These services are required for some or all parts of the PDK to work.
    #####

    /**
     * Adapter to make requests with.
     *
     * @see \MyParcelNL\Pdk\Tests\Api\Guzzle7ClientAdapter
     */

    ClientAdapterInterface::class => autowire(),

    /**
     * Handles cron jobs.
     */

    CronServiceInterface::class => autowire(),

    /**
     * Handles translations.
     *
     * @see \MyParcelNL\Pdk\Language\Service\AbstractLanguageService
     */

    LanguageServiceInterface::class => autowire(),

    /**
     * Handles logging.
     *
     * @see        \MyParcelNL\Pdk\Logger\AbstractLogger
     * @deprecated Will be removed in v3.0.0. Use PdkLoggerInterface instead.
     */

    LoggerInterface::class    => autowire(),

    /**
     * Handles logging.
     *
     * @see        \MyParcelNL\Pdk\Logger\AbstractLogger
     */
    PdkLoggerInterface::class => get(LoggerInterface::class),

    /**
     * Exposes frontend api url and endpoints.
     *
     * @note Required to use the checkout.
     * @see  \MyParcelNL\Pdk\App\Api\Frontend\AbstractFrontendEndpointService
     * @see  \MyParcelNL\Pdk\App\Api\Contract\EndpointServiceInterface
     */

    FrontendEndpointServiceInterface::class => autowire(),

    /**
     * Handles available order statuses in your app.
     *
     * @note Required for plugin settings. TODO: Remove this requirement.
     */

    OrderStatusServiceInterface::class => autowire(),

    /**
     * Defines the url and endpoints for webhooks.
     *
     * @note Required for plugin settings. TODO: Remove this requirement.
     * @see  \MyParcelNL\Pdk\App\Webhook\Service\AbstractPdkWebhookService
     */

    PdkWebhookServiceInterface::class => autowire(),

    /**
     * Handles views in your application, like detecting pages. The FrontendRenderService uses this service to determine where it should render components.
     *
     * @note Required for the admin frontend.
     * @see  \MyParcelNL\Pdk\Frontend\Service\AbstractViewService
     */

    ViewServiceInterface::class => autowire(),

    /**
     * Exposes backend api url and endpoints.
     *
     * @note Required for the admin frontend.
     * @see  \MyParcelNL\Pdk\App\Api\Backend\AbstractPdkBackendEndpointService
     * @see  \MyParcelNL\Pdk\App\Api\Contract\EndpointServiceInterface
     */

    BackendEndpointServiceInterface::class => autowire(),
];
