<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of CheckoutContext
 * @method CheckoutContext make()
 * @method $this withConfig(DeliveryOptionsConfig|DeliveryOptionsConfigFactory $config)
 * @method $this withSettings(array $settings)
 * @method $this withStrings($strings)
 */
final class CheckoutContextFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return CheckoutContext::class;
    }
}
