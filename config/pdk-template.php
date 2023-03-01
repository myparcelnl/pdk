<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Repository\AccountRepositoryInterface;
use MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface;
use MyParcelNL\Pdk\Base\CronServiceInterface;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;
use MyParcelNL\Pdk\Plugin\Api\Backend\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Api\Frontend\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Repository\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Repository\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Repository\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Service\CheckoutServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\OrderStatusServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\ViewServiceInterface;
use MyParcelNL\Pdk\Plugin\Webhook\PdkWebhookServiceInterface;
use MyParcelNL\Pdk\Plugin\Webhook\Repository\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Settings\Repository\SettingsRepositoryInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\value;

/**
 * Template for the config/pdk.php file.
 */
return [
    /**
     * Information about the app that is using the PDK.
     */
    'appInfo'   => value([
        'name'    => null,
        'title'   => null,
        'path'    => null,
        'url'     => null,
        'version' => null,
    ]),

    /**
     * User agent to pass to requests.
     */
    'userAgent' => value([]),

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

    AccountRepositoryInterface::class => autowire(),

    /**
     * Handles conversion of your app's order data to PDK order data.
     *
     * @see \MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository
     */

    PdkOrderRepositoryInterface::class => autowire(),

    /**
     * Handles webhook data being saved and retrieved in your app.
     *
     * @note Required for plugin settings.
     * @see  \MyParcelNL\Pdk\Plugin\Webhook\Repository\AbstractPdkWebhooksRepository
     */

    PdkWebhooksRepositoryInterface::class => autowire(),

    /**
     * Handles settings being saved and retrieved in your app.
     *
     * @note Required for plugin settings.
     * @see  \MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository
     */

    SettingsRepositoryInterface::class => autowire(),

    /**
     * Handles conversion of your app's cart data to PDK cart data.
     *
     * @note Required for using the checkout.
     * @see  \MyParcelNL\Pdk\Plugin\Repository\AbstractPdkCartRepository
     */

    PdkCartRepositoryInterface::class => autowire(),

    /**
     * Handles conversion of your app's shipping method data to PDK shipping method data.
     *
     * @note Required for using the checkout.
     * @see  \MyParcelNL\Pdk\Plugin\Repository\AbstractPdkShippingMethodRepository
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
     * @see \MyParcelNL\Pdk\Logger\AbstractLogger
     */

    LoggerInterface::class => autowire(),

    /**
     * Gets checkout data.
     *
     * @note Required for using the checkout.
     */

    CheckoutServiceInterface::class => autowire(),

    /**
     * Exposes frontend api url and endpoints.
     *
     * @note Required to use the checkout.
     * @see  \MyParcelNL\Pdk\Plugin\Api\Frontend\AbstractFrontendEndpointService
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
     * @see  \MyParcelNL\Pdk\Plugin\Webhook\AbstractPdkWebhookService
     */

    PdkWebhookServiceInterface::class => autowire(),

    /**
     * Handles views in your application, like detecting pages. The RenderService uses this service to determine where it should render components.
     *
     * @note Required for the admin frontend.
     * @see  \MyParcelNL\Pdk\Plugin\Service\AbstractViewService
     */

    ViewServiceInterface::class => autowire(),

    /**
     * Exposes backend api url and endpoints.
     *
     * @note Required for the admin frontend.
     * @see  \MyParcelNL\Pdk\Plugin\Api\Backend\AbstractPdkBackendEndpointService
     */

    BackendEndpointServiceInterface::class => autowire(),
];
