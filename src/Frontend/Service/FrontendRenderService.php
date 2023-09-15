<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Context\Contract\ContextServiceInterface;
use MyParcelNL\Pdk\Context\Model\ContextBag;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Contract\FrontendRenderServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;
use MyParcelNL\Sdk\src\Support\Str;
use Throwable;

class FrontendRenderService implements FrontendRenderServiceInterface
{
    final public const BOOTSTRAP_CONTAINER_ID = 'myparcel-pdk-boot';
    /**
     * Ids and events
     */
    final public const BOOTSTRAP_RENDER_EVENT_PING = 'myparcel_pdk_ping';
    final public const BOOTSTRAP_RENDER_EVENT_PONG = 'myparcel_pdk_pong';
    /**
     * Delivery options
     */
    protected const COMPONENT_DELIVERY_OPTIONS = 'DeliveryOptions';
    /**
     * Frontend components
     */
    protected const COMPONENT_INIT_SCRIPT            = 'init';
    protected const COMPONENT_CHILD_PRODUCT_SETTINGS = 'ChildProductSettings';
    protected const COMPONENT_MODALS                 = 'Modals';
    protected const COMPONENT_NOTIFICATIONS          = 'Notifications';
    protected const COMPONENT_ORDER_BOX              = 'OrderBox';
    protected const COMPONENT_ORDER_LIST_ITEM        = 'OrderListItem';
    protected const COMPONENT_PLUGIN_SETTINGS        = 'PluginSettings';
    protected const COMPONENT_PRODUCT_SETTINGS       = 'ProductSettings';

    /**
     * @var string
     */
    private static $jsInitTemplate;

    /**
     * @var string
     */
    private static $renderTemplate;

    /**
     * @var \MyParcelNL\Pdk\Context\Contract\ContextServiceInterface
     */
    protected $contextService;

    public function __construct(
        ContextServiceInterface               $contextService,
        private readonly ViewServiceInterface $viewService,
        private readonly FileSystemInterface  $fileSystem
    ) {
        $this->contextService = $contextService;
    }

    public function renderChildProductSettings(PdkProduct $product): string
    {
        if (! $product->parent) {
            throw new InvalidArgumentException('Product is not a child product');
        }

        return $this->renderComponent(
            self::COMPONENT_CHILD_PRODUCT_SETTINGS,
            [Context::ID_PRODUCT_DATA],
            ['product' => $product]
        );
    }

    public function renderDeliveryOptions(PdkCart $cart): string
    {
        return $this->renderComponent(self::COMPONENT_DELIVERY_OPTIONS, [
            Context::ID_CHECKOUT,
        ], ['cart' => $cart]);
    }

    /**
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
     * @noinspection PhpUnused
     */
    public function renderModals(): string
    {
        return $this->renderComponent(self::COMPONENT_MODALS);
    }

    /**
     * @noinspection PhpUnused
     */
    public function renderNotifications(): string
    {
        return $this->renderComponent(self::COMPONENT_NOTIFICATIONS);
    }

    /**
     * @noinspection PhpUnused
     */
    public function renderOrderBox(PdkOrder $order): string
    {
        return $this->renderComponent(self::COMPONENT_ORDER_BOX, [
            Context::ID_ORDER_DATA,
        ], ['order' => $order]);
    }

    /**
     * @noinspection PhpUnused
     */
    public function renderOrderListItem(PdkOrder $order): string
    {
        return $this->renderComponent(self::COMPONENT_ORDER_LIST_ITEM, [Context::ID_ORDER_DATA], ['order' => $order]);
    }

    public function renderPluginSettings(): string
    {
        return $this->renderComponent(self::COMPONENT_PLUGIN_SETTINGS, [Context::ID_PLUGIN_SETTINGS_VIEW]);
    }

    public function renderProductSettings(PdkProduct $product): string
    {
        return $this->renderComponent(
            self::COMPONENT_PRODUCT_SETTINGS,
            [Context::ID_PRODUCT_DATA, Context::ID_PRODUCT_SETTINGS_VIEW],
            ['product' => $product]
        );
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Context\Model\ContextBag $context
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function encodeContext(?ContextBag $context): string
    {
        return $context
            ? htmlspecialchars(
                json_encode(array_filter($context->toArrayWithoutNull()), JSON_THROW_ON_ERROR),
                ENT_QUOTES,
                'UTF-8'
            )
            : '{}';
    }

    protected function getJavaScriptInitTemplate(): string
    {
        if (! self::$jsInitTemplate) {
            self::$jsInitTemplate = $this->fileSystem->get($this->getTemplate(sprintf('init.%s.html', Pdk::getMode())));
        }

        return self::$jsInitTemplate;
    }

    protected function getRenderTemplate(): string
    {
        if (! self::$renderTemplate) {
            self::$renderTemplate = $this->fileSystem->get($this->getTemplate('context.html'));
        }

        return self::$renderTemplate;
    }

    protected function renderComponent(string $component, array $contexts = [], array $arguments = []): string
    {
        if (! $this->shouldRender($component)) {
            return '';
        }

        return $this->renderTemplate(
            $this->getRenderTemplate(),
            [
                '__ID__'         => sprintf('pdk-%s-%s', Str::kebab($component), random_int(0, mt_getrandmax())),
                '__COMPONENT__'  => $component,
                '__EVENT_PING__' => self::BOOTSTRAP_RENDER_EVENT_PING,
                '__EVENT_PONG__' => self::BOOTSTRAP_RENDER_EVENT_PONG,
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
            Logger::error(
                $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    ...compact(
                        'template',
                        'contexts',
                        'templateParameters',
                        'contextArguments'
                    ),
                ]
            );

            return '';
        }
    }

    protected function requiresAccount(string $component): bool
    {
        return match ($component) {
            self::COMPONENT_INIT_SCRIPT, self::COMPONENT_MODALS, self::COMPONENT_NOTIFICATIONS, self::COMPONENT_PLUGIN_SETTINGS => false,
            default => true,
        };
    }

    protected function shouldRender(string $component): bool
    {
        if ($this->requiresAccount($component) && ! AccountSettings::getAccount()) {
            return false;
        }

        return match ($component) {
            self::COMPONENT_CHILD_PRODUCT_SETTINGS => $this->viewService->isChildProductPage(),
            self::COMPONENT_DELIVERY_OPTIONS => $this->viewService->isCheckoutPage(),
            self::COMPONENT_INIT_SCRIPT => $this->viewService->isAnyPdkPage(),
            self::COMPONENT_MODALS => $this->viewService->hasModals(),
            self::COMPONENT_NOTIFICATIONS => $this->viewService->hasNotifications(),
            self::COMPONENT_ORDER_BOX => $this->viewService->isOrderPage(),
            self::COMPONENT_ORDER_LIST_ITEM => $this->viewService->isOrderListPage(),
            self::COMPONENT_PLUGIN_SETTINGS => $this->viewService->isPluginSettingsPage(),
            self::COMPONENT_PRODUCT_SETTINGS => $this->viewService->isProductPage(),
            default => throw new InvalidArgumentException("Unknown component: $component"),
        };
    }

    private function getTemplate(string $template): string
    {
        return sprintf('%ssrc/Frontend/Template/%s', Pdk::get('rootDir'), $template);
    }
}
