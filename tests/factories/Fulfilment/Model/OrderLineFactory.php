<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of OrderLine
 * @method OrderLine make()
 * @method $this withInstructions(string $instructions)
 * @method $this withPrice(int $price)
 * @method $this withPriceAfterVat(int $priceAfterVat)
 * @method $this withProduct(Product|ProductFactory $product)
 * @method $this withQuantity(int $quantity)
 * @method $this withShippable(bool $shippable)
 * @method $this withUuid(string $uuid)
 * @method $this withVat(int $vat)
 */
final class OrderLineFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return OrderLine::class;
    }
}
