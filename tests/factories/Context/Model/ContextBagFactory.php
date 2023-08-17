<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Context\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Context\Collection\ProductDataContextCollection;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of ContextBag
 * @method ContextBag make()
 * @method $this withCheckout(CheckoutContext|CheckoutContextFactory $checkout)
 * @method $this withDynamic(DynamicContext|DynamicContextFactory $dynamic)
 * @method $this withGlobal(GlobalContext|GlobalContextFactory $global)
 * @method $this withOrderData(OrderDataContextCollection|OrderDataContextFactory[] $orderData)
 * @method $this withPluginSettingsView(PluginSettingsViewContext $pluginSettingsView)
 * @method $this withProductData(ProductDataContextCollection $productData)
 * @method $this withProductSettingsView(ProductSettingsViewContext|ProductSettingsViewContextFactory $productSettingsView)
 */
final class ContextBagFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return ContextBag::class;
    }
}
