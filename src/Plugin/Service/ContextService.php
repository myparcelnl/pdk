<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Contract\ContextServiceInterface;
use MyParcelNL\Pdk\Plugin\Model\Context\CheckoutContext;
use MyParcelNL\Pdk\Plugin\Model\Context\ContextBag;
use MyParcelNL\Pdk\Plugin\Model\Context\DynamicContext;
use MyParcelNL\Pdk\Plugin\Model\Context\GlobalContext;
use MyParcelNL\Pdk\Plugin\Model\Context\PluginSettingsViewContext;
use MyParcelNL\Pdk\Plugin\Model\Context\ProductSettingsViewContext;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;

class ContextService implements ContextServiceInterface
{
    /**
     * @param  null|\MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\CheckoutContext
     */
    public function createCheckoutContext(?PdkCart $cart): CheckoutContext
    {
        return $cart ? CheckoutContext::fromCart($cart) : new CheckoutContext();
    }

    /**
     * @param  array $contexts
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\ContextBag
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
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\DynamicContext
     */
    public function createDynamicContext(): DynamicContext
    {
        return new DynamicContext();
    }

    /**
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\GlobalContext
     */
    public function createGlobalContext(): GlobalContext
    {
        return new GlobalContext();
    }

    /**
     * @param  null|array|PdkOrder|PdkOrderCollection $orderData
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection
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
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\PluginSettingsViewContext
     */
    public function createPluginSettingsViewContext(): PluginSettingsViewContext
    {
        return new PluginSettingsViewContext();
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Plugin\Model\PdkProduct $product
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\ProductSettingsViewContext
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

        DefaultLogger::alert('Invalid context key passed.', compact('contextId', 'data'));
        return null;
    }
}
