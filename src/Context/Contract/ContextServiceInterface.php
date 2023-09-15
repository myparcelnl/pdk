<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Contract;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Context\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Context\Collection\ProductDataContextCollection;
use MyParcelNL\Pdk\Context\Model\CheckoutContext;
use MyParcelNL\Pdk\Context\Model\ContextBag;
use MyParcelNL\Pdk\Context\Model\DynamicContext;
use MyParcelNL\Pdk\Context\Model\GlobalContext;
use MyParcelNL\Pdk\Context\Model\PluginSettingsViewContext;
use MyParcelNL\Pdk\Context\Model\ProductSettingsViewContext;

interface ContextServiceInterface
{
    public function createCheckoutContext(PdkCart $cart): CheckoutContext;

    public function createContexts(array $contexts, array $data = []): ContextBag;

    public function createDynamicContext(): DynamicContext;

    public function createGlobalContext(): GlobalContext;

    /**
     * @param  null|array|PdkOrder|PdkOrderCollection $orderData
     */
    public function createOrderDataContext($orderData): OrderDataContextCollection;

    public function createPluginSettingsViewContext(): PluginSettingsViewContext;

    /**
     * @param  null|array|PdkProduct|PdkProductCollection $productData
     */
    public function createProductDataContext($productData): ProductDataContextCollection;

    public function createProductSettingsViewContext(): ProductSettingsViewContext;
}
