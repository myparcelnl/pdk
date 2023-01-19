<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Model\Context\CheckoutContext;
use MyParcelNL\Pdk\Plugin\Model\Context\ContextBag;
use MyParcelNL\Pdk\Plugin\Model\Context\DynamicContext;
use MyParcelNL\Pdk\Plugin\Model\Context\GlobalContext;
use MyParcelNL\Pdk\Plugin\Model\Context\PluginSettingsViewContext;
use MyParcelNL\Pdk\Plugin\Model\Context\ProductSettingsViewContext;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;

interface ContextServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\CheckoutContext
     */
    public function createCheckoutContext(PdkCart $cart): CheckoutContext;

    /**
     * @param  array $contexts
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\ContextBag
     */
    public function createContexts(array $contexts, array $data = []): ContextBag;

    /**
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\DynamicContext
     */
    public function createDynamicContext(): DynamicContext;

    /**
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\GlobalContext
     */
    public function createGlobalContext(): GlobalContext;

    /**
     * @param  null|PdkOrder|PdkOrderCollection $orderData
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection
     */
    public function createOrderDataContext($orderData): OrderDataContextCollection;

    /**
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\PluginSettingsViewContext
     */
    public function createPluginSettingsViewContext(): PluginSettingsViewContext;

    /**
     * @param  null|\MyParcelNL\Pdk\Plugin\Model\PdkProduct $product
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\Context\ProductSettingsViewContext
     */
    public function createProductSettingsViewContext(?PdkProduct $product): ProductSettingsViewContext;
}
