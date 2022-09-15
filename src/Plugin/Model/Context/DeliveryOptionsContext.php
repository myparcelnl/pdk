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

    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        $this->strings = Settings::get('checkout.strings');

        if (! isset($data['order'])) {
            return;
        }

        $this->fillOrderData($data['order']);
    }

    public function fillOrderData(PdkOrder $pdkOrder): void
    {
        $this->config = new DeliveryOptionsConfig(['order' => $pdkOrder]);
    }
}
