<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Settings;

/**
 * @property array{string,string}                                      $strings
 * @property null|\MyParcelNL\Pdk\Plugin\Model\Context\DeliveryOptions $config
 */
class DeliveryOptionsContext extends Model
{
    public    $attributes = [
        'strings' => [],
        'config'  => null,
    ];

    protected $casts      = [
        'strings' => 'array',
        'config'  => DeliveryOptions::class,
    ];

    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        $this->strings = Settings::get('checkout.strings');

        if (isset($data['order']) && ! isset($data['config'])) {
            $this->config = new DeliveryOptions(['order' => $data['order']]);
        }
    }
}
