<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Contract;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentOptionsCollection;

interface DeliveryOptionsServiceInterface
{
    /**
     * Creates an array with the packageType and carrierSettings key of the delivery options config.
     */
    public function createAllCarrierSettings(PdkCart $cart): array;

    /**
     * Calculate, per carrier, the shipment options this cart would be exported with — the same
     * settings chain and capabilities rules the real export runs — so the checkout can show and
     * lock options that are already decided on the merchant side (for example 18+ forcing
     * signature and only recipient on). The collection is keyed by legacy carrier identifier.
     */
    public function createCartShipmentOptions(PdkCart $cart): ShipmentOptionsCollection;
}
