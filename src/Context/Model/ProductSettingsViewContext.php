<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\View\ProductSettingsView;

/**
 * @property ProductSettingsView $view
 */
class ProductSettingsViewContext extends Model
{
    public    $attributes = [
        'view' => null,
    ];

    protected $casts      = [
        'view' => ProductSettingsView::class,
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        $this->attributes['view'] = Pdk::get(ProductSettingsView::class);
    }
}
