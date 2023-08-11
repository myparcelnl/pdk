<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\App\Cart\Collection\PdkCartFeeCollection;
use MyParcelNL\Pdk\App\Cart\Model\PdkCartFee;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsFeesServiceInterface;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Sdk\src\Support\Str;

class DeliveryOptionsFeesService implements DeliveryOptionsFeesServiceInterface
{
    private const FRONTEND_SHIPMENT_OPTIONS = [
        ShipmentOptions::ONLY_RECIPIENT,
        ShipmentOptions::SIGNATURE,
    ];

    /**
     * @param  string                                         $identifier
     * @param  \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     *
     * @return \MyParcelNL\Pdk\App\Cart\Model\PdkCartFee
     */
    public function createFee(string $identifier, DeliveryOptions $deliveryOptions): PdkCartFee
    {
        $translation     = Str::snake("delivery_options_{$identifier}_title");
        $priceSettingKey = implode('.', [
            CarrierSettings::ID,
            $deliveryOptions->carrier->externalIdentifier,
            Str::camel("price_$identifier"),
        ]);

        $amount = Settings::get($priceSettingKey);

        return new PdkCartFee([
            'id'          => $identifier,
            'translation' => $translation,
            'amount'      => $amount,
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     *
     * @return \MyParcelNL\Pdk\App\Cart\Collection\PdkCartFeeCollection
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function getFees(DeliveryOptions $deliveryOptions): PdkCartFeeCollection
    {
        return new PdkCartFeeCollection(
            array_merge(
                [$this->getDeliveryTypeFee($deliveryOptions)],
                $this->getShipmentOptionsFees($deliveryOptions)
            )
        );
    }

    private function getDeliveryTypeFee(DeliveryOptions $deliveryOptions): PdkCartFee
    {
        $deliveryType = $deliveryOptions->deliveryType;

        if ($deliveryOptions->shipmentOptions->sameDayDelivery) {
            $deliveryType = 'same_day';
        }

        return $this->createFee(Str::snake("delivery_type_$deliveryType"), $deliveryOptions);
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getShipmentOptionsFees(DeliveryOptions $deliveryOptions): array
    {
        $fees = [];

        foreach ($deliveryOptions->shipmentOptions->toArrayWithoutNull() as $key => $option) {
            if (! in_array($key, self::FRONTEND_SHIPMENT_OPTIONS, true)) {
                continue;
            }

            if (false === $option) {
                continue;
            }

            $fees[] = $this->createFee(Str::snake($key), $deliveryOptions);
        }

        return $fees;
    }
}
