<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\View\ProductSettingsView;

/**
 * @property PdkProduct          $product
 * @property ProductSettingsView $view
 * @property array               $values
 */
class ProductSettingsViewContext extends Model
{
    public    $attributes = [
        'product' => null,
        'view'    => null,
        'values'  => [],
    ];

    protected $casts      = [
        'product' => PdkProduct::class,
        'view'    => ProductSettingsView::class,
        'values'  => 'array',
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        $this->attributes['view'] = Pdk::get(ProductSettingsView::class);
    }

    /**
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @noinspection PhpUnused
     */
    public function getValuesAttribute(): array
    {
        return $this->product->settings->toArray();
    }

    /**
     * @param  mixed $product
     *
     * @noinspection PhpUnused
     */
    public function setProductAttribute($product): self
    {
        if (! $product instanceof PdkProduct) {
            $product = new PdkProduct($product);
        }

        $this->attributes['product'] = $product;

        return $this;
    }
}
