<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierFactory;

/**
 * @property null|string                                                 $apiKey
 * @property null|\MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier      $carrier
 * @property null|\MyParcelNL\Pdk\Shipment\Model\Options\DeliveryOptions $deliveryOptions
 * @property null|\MyParcelNL\Pdk\Base\Model\Address                     $recipient
 * @property null|\MyParcelNL\Pdk\Base\Model\Address                     $sender
 * @property null|\MyParcelNL\Sdk\src\Model\Fulfilment\Order             $order
 */
class Shipment extends Model
{
    protected $attributes = [
        'apiKey'          => null,
        'carrier'         => null,
        'deliveryOptions' => null,
        'recipient'       => null,
        'sender'          => null,
        'order'           => null,
    ];

    /**
     * Carrier is passed to the delivery options.
     *
     * @param  array $data
     *
     * @throws \Exception
     */
    public function __construct(array $data = [])
    {
        if (! isset($data['carrier'])) {
            throw new InvalidArgumentException('You must pass a carrier');
        }

        $data['carrier'] = CarrierFactory::create($data['carrier']);

        parent::__construct($data);

        $this->setDeliveryOptionsCarrier();
    }

    /**
     * @param  null|\MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier $carrier
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     */
    public function setCarrierAttribute(?AbstractCarrier $carrier): self
    {
        $this->attributes['carrier'] = $carrier;
        $this->setDeliveryOptionsCarrier();
        return $this;
    }

    /**
     * @return void
     */
    private function setDeliveryOptionsCarrier(): void
    {
        // In case the model hasn't fully initialized yet (e.g. in the constructor).
        if (! $this->deliveryOptions || is_string($this->deliveryOptions)) {
            return;
        }

        $this->deliveryOptions->carrier = $this->carrier;
    }
}
