<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Service;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Context\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Context\Contract\ContextServiceInterface;
use MyParcelNL\Pdk\Context\Model\CheckoutContext;
use MyParcelNL\Pdk\Context\Model\ContextBag;
use MyParcelNL\Pdk\Context\Model\DynamicContext;
use MyParcelNL\Pdk\Context\Model\GlobalContext;
use MyParcelNL\Pdk\Context\Model\PluginSettingsViewContext;
use MyParcelNL\Pdk\Context\Model\ProductSettingsViewContext;
use MyParcelNL\Pdk\Facade\Logger;

class ContextService implements ContextServiceInterface
{
    /**
     * @param  null|\MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return \MyParcelNL\Pdk\Context\Model\CheckoutContext
     */
    public function createCheckoutContext(?PdkCart $cart): CheckoutContext
    {
        return $cart ? CheckoutContext::fromCart($cart) : new CheckoutContext();
    }

    /**
     * @param  array $contexts
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Context\Model\ContextBag
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function createContexts(array $contexts, array $data = []): ContextBag
    {
        $context = array_reduce($contexts, function (array $acc, string $id) use ($data) {
            $acc[$id] = $this->resolveContext($id, $data);

            return $acc;
        }, []);

        return new ContextBag($context);
    }

    /**
     * @return \MyParcelNL\Pdk\Context\Model\DynamicContext
     */
    public function createDynamicContext(): DynamicContext
    {
        return new DynamicContext();
    }

    /**
     * @return \MyParcelNL\Pdk\Context\Model\GlobalContext
     */
    public function createGlobalContext(): GlobalContext
    {
        return new GlobalContext();
    }

    /**
     * @param  null|array|PdkOrder|PdkOrderCollection $orderData
     *
     * @return \MyParcelNL\Pdk\Context\Collection\OrderDataContextCollection
     */
    public function createOrderDataContext($orderData): OrderDataContextCollection
    {
        if (is_a($orderData, PdkOrder::class) || (is_array($orderData) && Arr::isAssoc($orderData))) {
            $orderData = [$orderData];
        }

        $orders = is_a($orderData, PdkOrderCollection::class)
            ? $orderData
            : new PdkOrderCollection($orderData);

        return new OrderDataContextCollection($orders ? $orders->all() : null);
    }

    /**
     * @return \MyParcelNL\Pdk\Context\Model\PluginSettingsViewContext
     */
    public function createPluginSettingsViewContext(): PluginSettingsViewContext
    {
        return new PluginSettingsViewContext();
    }

    /**
     * @param  null|\MyParcelNL\Pdk\App\Order\Model\PdkProduct $product
     *
     * @return \MyParcelNL\Pdk\Context\Model\ProductSettingsViewContext
     */
    public function createProductSettingsViewContext(?PdkProduct $product): ProductSettingsViewContext
    {
        return new ProductSettingsViewContext(['product' => $product]);
    }

    /**
     * @param  string $contextId
     * @param  array  $data
     *
     * @return null|GlobalContext|DynamicContext|OrderDataContextCollection|PluginSettingsViewContext|ProductSettingsViewContext|CheckoutContext
     */
    protected function resolveContext(string $contextId, array $data = [])
    {
        switch ($contextId) {
            case Context::ID_GLOBAL:
                return $this->createGlobalContext();

            case Context::ID_DYNAMIC:
                return $this->createDynamicContext();

            case Context::ID_ORDER_DATA:
                return $this->createOrderDataContext($data['order'] ?? null);

            case Context::ID_PLUGIN_SETTINGS_VIEW:
                return $this->createPluginSettingsViewContext();

            case Context::ID_PRODUCT_SETTINGS_VIEW:
                return $this->createProductSettingsViewContext($data['product'] ?? null);

            case Context::ID_CHECKOUT:
                return $this->createCheckoutContext($data['cart'] ?? null);
        }

        Logger::alert('Invalid context key passed.', compact('contextId', 'data'));
        return null;
    }
}
