<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Model\Context\ContextBag;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

class RenderService implements RenderServiceInterface
{
    /**
     * Ids and events
     */
    public const  BOOTSTRAP_RENDER_EVENT      = 'myparcel_pdk_loaded';
    public const  BOOTSTRAP_DATA_CONTAINER_ID = 'myparcel-pdk-bootstrap';
    /**
     * Components
     */
    private const COMPONENT_MODALS            = 'Modals';
    private const COMPONENT_NOTIFICATIONS     = 'Notifications';
    private const COMPONENT_ORDER_CARD        = 'OrderCard';
    private const COMPONENT_ORDER_LIST_COLUMN = 'OrderListColumn';

    /**
     * @var string
     */
    private static $jsInitTemplate;

    /**
     * @var string
     */
    private static $renderTemplate;

    /**
     * @var \MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface
     */
    private $contextService;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface $contextService
     */
    public function __construct(ContextServiceInterface $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * @return string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @noinspection PhpUnused
     */
    public function renderInitScript(): string
    {
        $context = $this->contextService->createContexts([Context::ID_GLOBAL]);

        return strtr($this->getJavaScriptInitTemplate(), [
            '__BOOTSTRAP_CONTAINER_ID__' => self::BOOTSTRAP_DATA_CONTAINER_ID,
            '__CONTEXT__'                => $this->encodeContext($context),
        ]);
    }

    /**
     * @return string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @noinspection PhpUnused
     */
    public function renderModals(): string
    {
        return $this->render(self::COMPONENT_MODALS);
    }

    /**
     * @return string
     * @noinspection PhpUnused
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function renderNotifications(): string
    {
        return $this->render(self::COMPONENT_NOTIFICATIONS);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @noinspection PhpUnused
     */
    public function renderOrderCard(PdkOrder $order): string
    {
        $contextBag = $this->contextService->createContexts([
            Context::ID_ORDER_DATA,
        ], ['order' => $order]);

        return $this->render(self::COMPONENT_ORDER_CARD, $contextBag);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @noinspection PhpUnused
     */
    public function renderOrderListColumn(PdkOrder $order): string
    {
        $contextBag = $this->contextService->createContexts([
            Context::ID_ORDER_DATA,
        ], ['order' => $order]);

        return $this->render(self::COMPONENT_ORDER_LIST_COLUMN, $contextBag);
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
     * @param  string                                               $method
     * @param  null|\MyParcelNL\Pdk\Plugin\Model\Context\ContextBag $context
     *
     * @return string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function render(string $method, ?ContextBag $context = null): string
    {
        return strtr($this->getRenderTemplate(), [
            '__ID__'        => sprintf('pdk-%s', mt_rand()),
            '__COMPONENT__' => $method,
            '__CONTEXT__'   => $this->encodeContext($context),
            '__EVENT__'     => self::BOOTSTRAP_RENDER_EVENT,
        ]);
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Plugin\Model\Context\ContextBag $context
     *
     * @return string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeContext(?ContextBag $context): string
    {
        return $context ? htmlspecialchars(json_encode(array_filter($context->toArray())), ENT_QUOTES, 'UTF-8') : '{}';
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
