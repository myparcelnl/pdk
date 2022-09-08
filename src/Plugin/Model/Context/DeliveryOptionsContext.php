<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

/**
 * @property array{string,string}                                       $strings
 * @property \MyParcelNL\Pdk\Plugin\Model\Context\DeliveryOptionsConfig $config
 */
class DeliveryOptionsContext extends Model
{
    public    $attributes = [
        'strings' => null,
        'config'  => null,
    ];

    protected $casts      = [
        'strings' => 'array',
        'config'  => DeliveryOptionsConfig::class,
    ];

    public function fromOrder(PdkOrder $pdkOrder): self
    {
        $this->strings = Settings::get('checkout.strings');
        $this->config = new DeliveryOptionsConfig(['order' => $pdkOrder]);

        return $this;
    }
}
