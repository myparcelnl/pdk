<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Settings\View\ProductSettingsView;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;

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
    public function __construct(?array $data = [])
    {
        parent::__construct($data);

        $this->attributes['view'] = Pdk::get(ProductSettingsView::class);
    }

    /**
     * @param  mixed $product
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function setProductAttribute($product): self
    {
        if ($product instanceof PdkProduct) {
            $settings                    = $product->getSettings();
            $this->attributes['values']  = $settings->toArray();
            $this->attributes['product'] = $product;
        }

        return $this;
    }
}
