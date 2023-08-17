<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\CurrencyFactory;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of CustomsDeclarationItem
 * @method CustomsDeclarationItem make()
 * @method $this withAmount(int $amount)
 * @method $this withClassification(string $classification)
 * @method $this withCountry(string $country)
 * @method $this withDescription(string $description)
 * @method $this withItemValue(Currency|CurrencyFactory $itemValue)
 * @method $this withWeight(int $weight)
 */
final class CustomsDeclarationItemFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return CustomsDeclarationItem::class;
    }
}
