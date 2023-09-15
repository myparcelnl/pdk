<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of PdkOrderLine
 * @method PdkOrderLine make()
 * @method $this withPrice(int $price)
 * @method $this withPriceAfterVat(int $priceAfterVat)
 * @method $this withQuantity(int $quantity)
 * @method $this withVat(int $vat)
 */
final class PdkOrderLineFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return PdkOrderLine::class;
    }

    /**
     * @param  string|array|PdkProduct|PdkProductFactory $product
     *
     * @return $this
     */
    public function withProduct($product): self
    {
        if (is_scalar($product)) {
            $product = Pdk::get(PdkProductRepositoryInterface::class)
                ->getProduct($product);
        }

        return $this->with(['product' => $product]);
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
     */
    protected function save(Model $model): void
    {
        factory(PdkOrder::class)
            ->withLines(factory(PdkOrderLineCollection::class)->push($model))
            ->store();
    }
}
