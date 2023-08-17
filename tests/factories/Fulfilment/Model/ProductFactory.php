<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of Product
 * @method Product make()
 * @method $this withDescription(string $description)
 * @method $this withEan(string $ean)
 * @method $this withExternalIdentifier(string $externalIdentifier)
 * @method $this withHeight(int $height)
 * @method $this withLength(int $length)
 * @method $this withName(string $name)
 * @method $this withSku(string $sku)
 * @method $this withUuid(string $uuid)
 * @method $this withWeight(int $weight)
 * @method $this withWidth(int $width)
 */
final class ProductFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return Product::class;
    }
}
