<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Contract\ContextServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\RenderServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\ViewServiceInterface;
use MyParcelNL\Pdk\Plugin\Model\Context\ContextBag;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;
use MyParcelNL\Sdk\src\Support\Str;
use Throwable;

class RenderService implements RenderServiceInterface
{
    /**
     * Ids and events
     */
    public const  BOOTSTRAP_RENDER_EVENT = 'myparcel_pdk_loaded';
    public const  BOOTSTRAP_CONTAINER_ID = 'myparcel-pdk-boot';
    /**
     * Delivery options
     */
    protected const COMPONENT_DELIVERY_OPTIONS = 'DeliveryOptions';
    /**
     * Admin components
     */
    protected const COMPONENT_INIT_SCRIPT      = 'init';
    protected const COMPONENT_MODALS           = 'Modals';
    protected const COMPONENT_NOTIFICATIONS    = 'Notifications';
    protected const COMPONENT_ORDER_BOX        = 'OrderBox';
    protected const COMPONENT_ORDER_LIST_ITEM  = 'OrderListItem';
    protected const COMPONENT_PLUGIN_SETTINGS  = 'PluginSettings';
    protected const COMPONENT_PRODUCT_SETTINGS = 'ProductSettings';

    /**
     * @var string
     */
    private static $jsInitTemplate;

    /**
     * @var string
     */
    private static $renderTemplate;

    /**
     * @var \MyParcelNL\Pdk\Plugin\Contract\ContextServiceInterface
     */
    protected $contextService;

    /**
     * @var \MyParcelNL\Pdk\Plugin\Contract\ViewServiceInterface
     */
    private $viewService;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Contract\ContextServiceInterface $contextService
     * @param  \MyParcelNL\Pdk\Plugin\Contract\ViewServiceInterface    $viewService
     */
    public function __construct(ContextServiceInterface $contextService, ViewServiceInterface $viewService)
    {
        $this->contextService = $contextService;
        $this->viewService    = $viewService;
    }


    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return string
     */
    public function renderDeliveryOptions(PdkCart $cart): string
    {
        return $this->renderComponent(self::COMPONENT_DELIVERY_OPTIONS, [
            Context::ID_CHECKOUT,
        ], ['cart' => $cart]);
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function renderInitScript(): string
    {
        if (! $this->shouldRender(self::COMPONENT_INIT_SCRIPT)) {
            return '';
        }

        return $this->renderTemplate(
            $this->getJavaScriptInitTemplate(),
            ['__ID__' => self::BOOTSTRAP_CONTAINER_ID],
            [Context::ID_GLOBAL, Context::ID_DYNAMIC]
        );
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function renderModals(): string
    {
        return $this->renderComponent(self::COMPONENT_MODALS);
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function renderNotifications(): string
    {
        return $this->renderComponent(self::COMPONENT_NOTIFICATIONS);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function renderOrderBox(PdkOrder $order): string
    {
        return $this->renderComponent(self::COMPONENT_ORDER_BOX, [
            Context::ID_ORDER_DATA,
        ], ['order' => $order]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function renderOrderListItem(PdkOrder $order): string
    {
        return $this->renderComponent(self::COMPONENT_ORDER_LIST_ITEM, [Context::ID_ORDER_DATA], ['order' => $order]);
    }

    /**
     * @return string
     */
    public function renderPluginSettings(): string
    {
        return $this->renderComponent(self::COMPONENT_PLUGIN_SETTINGS, [Context::ID_PLUGIN_SETTINGS_VIEW]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkProduct $product
     *
     * @return string
     */
    public function renderProductSettings(PdkProduct $product): string
    {
        return $this->renderComponent(
            self::COMPONENT_PRODUCT_SETTINGS,
            [Context::ID_PRODUCT_SETTINGS_VIEW],
            ['product' => $product]
        );
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Plugin\Model\Context\ContextBag $context
     *
     * @return string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function encodeContext(?ContextBag $context): string
    {
        return $context ? htmlspecialchars(json_encode(array_filter($context->toArray())), ENT_QUOTES, 'UTF-8') : '{}';
    }

    /**
     * @return string
     */
    protected function getJavaScriptInitTemplate(): string
    {
        if (! self::$jsInitTemplate) {
            self::$jsInitTemplate = file_get_contents($this->getTemplate(sprintf('init.%s.html', Pdk::getMode())));
        }

        return self::$jsInitTemplate;
    }

    /**
     * @return string
     */
    protected function getRenderTemplate(): string
    {
        if (! self::$renderTemplate) {
            self::$renderTemplate = file_get_contents($this->getTemplate('context.html'));
        }

        return self::$renderTemplate;
    }

    /**
     * @param  string $component
     * @param  array  $contexts
     * @param  array  $arguments
     *
     * @return string
     */
    protected function renderComponent(string $component, array $contexts = [], array $arguments = []): string
    {
        if (! $this->shouldRender($component)) {
            return '';
        }

        return $this->renderTemplate(
            $this->getRenderTemplate(),
            [
                '__ID__'        => sprintf('pdk-%s-%s', Str::kebab($component), mt_rand()),
                '__COMPONENT__' => $component,
                '__EVENT__'     => self::BOOTSTRAP_RENDER_EVENT,
            ],
            $contexts,
            $arguments
        );
    }

    /**
     * @param  string $template           Html template
     * @param  array  $templateParameters Parameters to inject into the template
     * @param  array  $contexts           Contexts to generate and inject into the template
     * @param  array  $contextArguments   Arguments to pass when creating context
     *
     * @return string
     */
    protected function renderTemplate(
        string $template,
        array  $templateParameters = [],
        array  $contexts = [],
        array  $contextArguments = []
    ): string {
        try {
            $context = $this->contextService->createContexts($contexts, $contextArguments);

            return strtr($template, $templateParameters + ['__CONTEXT__' => $this->encodeContext($context)]);
        } catch (Throwable $e) {
            DefaultLogger::error($e->getMessage(), [
                    'exception' => $e,
                    'template'  => $template,
                    'contexts'  => $contexts,
                ]
            );
            return '';
        }
    }

    /**
     * @param  string $component
     *
     * @return bool
     */
    protected function shouldRender(string $component): bool
    {
        switch ($component) {
            case self::COMPONENT_INIT_SCRIPT:
                return $this->viewService->isAnyPdkPage();

            case self::COMPONENT_MODALS:
                return $this->viewService->hasModals();

            case self::COMPONENT_NOTIFICATIONS:
                return $this->viewService->hasNotifications();

            case self::COMPONENT_ORDER_BOX:
                return $this->viewService->isOrderPage();

            case self::COMPONENT_ORDER_LIST_ITEM:
                return $this->viewService->isOrderListPage();

            case self::COMPONENT_PLUGIN_SETTINGS:
                return $this->viewService->isPluginSettingsPage();

            case self::COMPONENT_PRODUCT_SETTINGS:
                return $this->viewService->isProductPage();

            case self::COMPONENT_DELIVERY_OPTIONS:
                return $this->viewService->isCheckoutPage();

            default:
                return true;
        }
    }

    /**
     * @param  string $template
     *
     * @return string
     */
    private function getTemplate(string $template): string
    {
        return sprintf('%ssrc/Plugin/Admin/Template/%s', Pdk::get('rootDir'), $template);
    }
}
