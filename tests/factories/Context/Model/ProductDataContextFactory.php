<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\App\Order\Model\PdkProductFactory;
use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\CurrencyFactory;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettingsFactory;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of ProductDataContext
 * @method ProductDataContext make()
 * @method $this withEan(string $ean)
 * @method $this withExternalIdentifier(string $externalIdentifier)
 * @method $this withHeight(int $height)
 * @method $this withIsDeliverable(bool $isDeliverable)
 * @method $this withLength(int $length)
 * @method $this withName(string $name)
 * @method $this withParent(array|PdkProduct|PdkProductFactory|PdkProductFactory $parent)
 * @method $this withPrice(array|Currency|CurrencyFactory|CurrencyFactory $price)
 * @method $this withSettings(array|ProductSettings|ProductSettingsFactory|ProductSettingsFactory $settings)
 * @method $this withSku(string $sku)
 * @method $this withWeight(int $weight)
 * @method $this withWidth(int $width)
 */
final class ProductDataContextFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return ProductDataContext::class;
    }
}
