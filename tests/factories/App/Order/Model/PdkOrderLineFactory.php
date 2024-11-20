<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\CurrencyFactory;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of PdkOrderLine
 * @method PdkOrderLine make()
 * @method $this withPrice(int|array|Currency|CurrencyFactory $price)
 * @method $this withPriceAfterVat(int|array|Currency|CurrencyFactory $priceAfterVat)
 * @method $this withQuantity(int $quantity)
 * @method $this withVat(int|array|Currency|CurrencyFactory $vat)
 */
final class PdkOrderLineFactory extends AbstractModelFactory
{
    use UsesCurrency;

    public function getModel(): string
    {
        return PdkOrderLine::class;
    }

    /**
     * @param  int|string|array|PdkProduct|PdkProductFactory $product
     *
     * @return $this
     */
    public function withProduct($product): self
    {
        if (is_scalar($product)) {
            /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface $productRepository */
            $productRepository = Pdk::get(PdkProductRepositoryInterface::class);

            return $this->withProduct($productRepository->getProduct($product));
        }

        if (is_array($product)) {
            return $this->withProduct(new PdkProduct($product));
        }

        if ($product instanceof FactoryInterface) {
            return $this->withProduct($product->make());
        }

        /** @var \MyParcelNL\Pdk\App\Order\Model\PdkProduct $product */
        return $this
            ->with(['product' => $product])
            ->withPrice($this->toInt($product->price));
    }

    public function withProductWithAllSettings(): self
    {
        return $this->withProduct(factory(PdkProduct::class)->withSettingsWithAllOptions());
    }

    protected function createDefault(): FactoryInterface
    {
        return $this->withProduct(factory(PdkProduct::class));
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrderLine $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        factory(PdkOrder::class)
            ->withLines(factory(PdkOrderLineCollection::class)->push($model))
            ->store();
    }
}
