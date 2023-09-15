<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\CurrencyFactory;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of CustomsDeclarationItem
 * @method CustomsDeclarationItem make()
 * @method $this withAmount(int $amount)
 * @method $this withClassification(string $classification)
 * @method $this withCountry(string $country)
 * @method $this withDescription(string $description)
 * @method $this withWeight(int $weight)
 */
final class CustomsDeclarationItemFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return CustomsDeclarationItem::class;
    }

    /**
     * @param  int|array|Currency|CurrencyFactory $itemValue
     */
    public function withItemValue($itemValue): self
    {
        if (is_int($itemValue)) {
            $itemValue = factory(Currency::class)->withAmount($itemValue);
        }

        return $this->with(['itemValue' => $itemValue]);
    }
}
