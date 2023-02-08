<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;

/**
 * @property \MyParcelNL\Pdk\Carrier\Model\Carrier             $carrier
 * @property \MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities $capabilities
 * @property \MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities $returnCapabilities
 */
class CarrierOptions extends Model
{
    protected $attributes = [
        'carrier'            => Carrier::class,
        'capabilities'       => CarrierCapabilities::class,
        'returnCapabilities' => CarrierCapabilities::class,
    ];

    protected $casts      = [
        'carrier'            => Carrier::class,
        'capabilities'       => CarrierCapabilities::class,
        'returnCapabilities' => CarrierCapabilities::class,
    ];

    /**
     * @param  null|array $data
     *
     * @throws \Exception
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        $this->loadCapabilities();
    }

    /**
     * @return void
     */
    private function loadCapabilities(): void
    {
        $allOptions = $this->carrier->getAllOptions();

        $this->fill(Arr::only($allOptions, ['capabilities', 'returnCapabilities']));
    }
}
