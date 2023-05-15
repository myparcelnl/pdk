<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Contract;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Context\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Context\Model\CheckoutContext;
use MyParcelNL\Pdk\Context\Model\ContextBag;
use MyParcelNL\Pdk\Context\Model\DynamicContext;
use MyParcelNL\Pdk\Context\Model\GlobalContext;
use MyParcelNL\Pdk\Context\Model\PluginSettingsViewContext;
use MyParcelNL\Pdk\Context\Model\ProductSettingsViewContext;

interface ContextServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return \MyParcelNL\Pdk\Context\Model\CheckoutContext
     */
    public function createCheckoutContext(PdkCart $cart): CheckoutContext;

    /**
     * @param  array $contexts
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Context\Model\ContextBag
     */
    public function createContexts(array $contexts, array $data = []): ContextBag;

    /**
     * @return \MyParcelNL\Pdk\Context\Model\DynamicContext
     */
    public function createDynamicContext(): DynamicContext;

    /**
     * @return \MyParcelNL\Pdk\Context\Model\GlobalContext
     */
    public function createGlobalContext(): GlobalContext;

    /**
     * @param  null|PdkOrder|PdkOrderCollection $orderData
     *
     * @return \MyParcelNL\Pdk\Context\Collection\OrderDataContextCollection
     */
    public function createOrderDataContext($orderData): OrderDataContextCollection;

    /**
     * @return \MyParcelNL\Pdk\Context\Model\PluginSettingsViewContext
     */
    public function createPluginSettingsViewContext(): PluginSettingsViewContext;

    /**
     * @param  null|\MyParcelNL\Pdk\App\Order\Model\PdkProduct $product
     *
     * @return \MyParcelNL\Pdk\Context\Model\ProductSettingsViewContext
     */
    public function createProductSettingsViewContext(?PdkProduct $product): ProductSettingsViewContext;
}
