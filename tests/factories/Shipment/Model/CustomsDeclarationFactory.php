<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Shipment\Collection\CustomsDeclarationItemCollection;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of CustomsDeclaration
 * @method CustomsDeclaration make()
 * @method $this withContents(int $contents)
 * @method $this withInvoice(string $invoice)
 * @method $this withItems(CustomsDeclarationItemCollection|CustomsDeclarationItemFactory[] $items)
 * @method $this withWeight(int $weight)
 */
final class CustomsDeclarationFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return CustomsDeclaration::class;
    }
}
