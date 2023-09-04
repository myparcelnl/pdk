<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\CurrencyFactory;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettingsFactory;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of PdkProduct
 * @method PdkProduct make()
 * @method $this withEan(string $ean)
 * @method $this withExternalIdentifier(string $externalIdentifier)
 * @method $this withHeight(int $height)
 * @method $this withIsDeliverable(bool $isDeliverable)
 * @method $this withLength(int $length)
 * @method $this withMergedSettings(array|ProductSettings|ProductSettingsFactory $mergedSettings)
 * @method $this withName(string $name)
 * @method $this withParent(array|PdkProduct|PdkProductFactory $parent)
 * @method $this withSettings(array|ProductSettings|ProductSettingsFactory $settings)
 * @method $this withSku(string $sku)
 * @method $this withWeight(int $weight)
 * @method $this withWidth(int $width)
 */
final class PdkProductFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return PdkProduct::class;
    }

    /**
     * @param  int|array|Currency|CurrencyFactory $price
     *
     * @return $this
     */
    public function withPrice($price): self
    {
        if (is_numeric($price)) {
            $price = factory(Currency::class)->withAmount($price);
        }

        return $this->with(['price' => $price]);
    }

    /**
     * @return $this
     */
    public function withSettingsWithAllOptions(): self
    {
        return $this->withSettings(factory(ProductSettings::class)->withAllOptions());
    }

    /**
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     */
    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withExternalIdentifier("PDK-{$this->getNextId()}")
            ->withSku('test')
            ->withName('test')
            ->withPrice(1000);
    }

    /**
     * @param  T $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkProductRepository $productRepository */
        $productRepository = Pdk::get(PdkProductRepositoryInterface::class);

        $productRepository->add(new PdkProductCollection([$model]));
    }
}
