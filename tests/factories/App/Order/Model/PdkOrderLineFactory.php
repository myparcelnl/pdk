<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of PdkOrderLine
 * @method PdkOrderLine make()
 * @method $this withPrice(int $price)
 * @method $this withPriceAfterVat(int $priceAfterVat)
 * @method $this withProduct(array|PdkProduct|PdkProductFactory $product)
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
     * @return $this
     */
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
