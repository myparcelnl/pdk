<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Service;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Context\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Context\Collection\ProductDataContextCollection;
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
        return $this->createContextCollection(
            $orderData,
            PdkOrder::class,
            PdkOrderCollection::class,
            OrderDataContextCollection::class
        );
    }

    /**
     * @return \MyParcelNL\Pdk\Context\Model\PluginSettingsViewContext
     */
    public function createPluginSettingsViewContext(): PluginSettingsViewContext
    {
        return new PluginSettingsViewContext();
    }

    /**
     * @param  null|PdkProduct|PdkProductCollection $productData
     *
     * @return \MyParcelNL\Pdk\Context\Collection\ProductDataContextCollection
     */
    public function createProductDataContext($productData): ProductDataContextCollection
    {
        return $this->createContextCollection(
            $productData,
            PdkProduct::class,
            PdkProductCollection::class,
            ProductDataContextCollection::class
        );
    }

    /**
     * @return \MyParcelNL\Pdk\Context\Model\ProductSettingsViewContext
     */
    public function createProductSettingsViewContext(): ProductSettingsViewContext
    {
        return new ProductSettingsViewContext();
    }

    /**
     * @param  null|array|Model|Collection $input
     * @param  class-string<Model>         $modelClass
     * @param  class-string<Collection>    $modelCollectionClass
     * @param  class-string<Collection>    $contextCollectionClass
     *
     * @return \MyParcelNL\Pdk\Context\Collection\ProductDataContextCollection
     */
    protected function createContextCollection(
        $input,
        string $modelClass,
        string $modelCollectionClass,
        string $contextCollectionClass
    ): Collection {
        /** @noinspection PhpUnnecessaryParenthesesInspection */
        if (is_a($input, $modelClass) || (is_array($input) && Arr::isAssoc($input))) {
            $input = [$input];
        }

        $collection = is_a($input, $modelCollectionClass)
            ? $input
            : new $modelCollectionClass($input);

        return new $contextCollectionClass($collection->all());
    }

    /**
     * @param  string $contextId
     * @param  array  $data
     *
     * @return null|CheckoutContext|DynamicContext|GlobalContext|OrderDataContextCollection|PluginSettingsViewContext|ProductDataContextCollection|ProductSettingsViewContext
     * @noinspection MultipleReturnStatementsInspection
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

            case Context::ID_PRODUCT_DATA:
                return $this->createProductDataContext($data['product'] ?? null);

            case Context::ID_PRODUCT_SETTINGS_VIEW:
                return $this->createProductSettingsViewContext();

            case Context::ID_CHECKOUT:
                return $this->createCheckoutContext($data['cart'] ?? null);
        }

        Logger::alert('Invalid context key passed.', compact('contextId', 'data'));
        return null;
    }
}
