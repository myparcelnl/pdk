<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Frontend\View\ProductSettingsView;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of ProductSettingsViewContext
 * @method ProductSettingsViewContext make()
 * @method $this withView(ProductSettingsView $view)
 */
final class ProductSettingsViewContextFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return ProductSettingsViewContext::class;
    }
}
