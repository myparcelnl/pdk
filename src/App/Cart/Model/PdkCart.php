<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Model;

use MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;

/**
 * @property string                 $externalIdentifier
 * @property PdkOrderLineCollection $lines
 * @property int                    $shipmentPrice
 * @property int                    $shipmentPriceAfterVat
 * @property int                    $shipmentVat
 * @property int                    $orderPrice
 * @property int                    $orderPriceAfterVat
 * @property int                    $orderVat
 * @property int                    $totalPrice
 * @property int                    $totalPriceAfterVat
 * @property int                    $totalVat
 * @property PdkShippingMethod      $shippingMethod
 */
class PdkCart extends Model
{
    protected $attributes = [
        'externalIdentifier'    => null,
        'lines'                 => PdkOrderLineCollection::class,
        'shipmentPrice'         => 0,
        'shipmentPriceAfterVat' => 0,
        'shipmentVat'           => 0,
        'orderPrice'            => 0,
        'orderPriceAfterVat'    => 0,
        'orderVat'              => 0,
        'totalPrice'            => 0,
        'totalPriceAfterVat'    => 0,
        'totalVat'              => 0,
        'shippingMethod'        => [],
    ];

    protected $casts      = [
        'externalIdentifier'    => 'string',
        'lines'                 => PdkOrderLineCollection::class,
        'shipmentPrice'         => 'int',
        'shipmentPriceAfterVat' => 'int',
        'shipmentVat'           => 'int',
        'orderPrice'            => 'int',
        'orderPriceAfterVat'    => 'int',
        'orderVat'              => 'int',
        'totalPrice'            => 'int',
        'totalPriceAfterVat'    => 'int',
        'totalVat'              => 'int',
        'shippingMethod'        => PdkShippingMethod::class,
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);
        $this->updateShippingMethod();
    }

    /**
     * @param  array|PdkShippingMethod $value
     *
     * @return self
     * @noinspection PhpUnused
     */
    public function setShippingMethodAttribute($value): self
    {
        $this->attributes['shippingMethod'] = $value;
        $this->updateShippingMethod();
        return $this;
    }

    /**
     * @return void
     */
    private function updateShippingMethod(): void
    {
        /** @var \MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface $service */
        $service = Pdk::get(CartCalculationServiceInterface::class);

        $this->shippingMethod = $service->calculateShippingMethod($this);
    }
}
