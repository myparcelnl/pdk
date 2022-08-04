<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation;

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use RuntimeException;

class Validator
{
    /**
     * @var \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     */
    private $order;

    /**
     * @var \MyParcelNL\Pdk\Validation\ValidationSchema
     */
    private $validationSchema;

    public function __construct(PdkOrder $order)
    {
        $this->order            = $order;
        $this->validationSchema = ValidationSchema::VALIDATION_SCHEMA;
    }

    /**
     * @return array
     */
    public function getAllowedOptions(): array
    {
        return $this->findValidationArray();
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Model\InvalidCastException
     */
    public function validate(): void
    {
        $arrayToValidate = $this->findValidationArray();

        $this->checkForInvalidOptions($arrayToValidate);
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Model\InvalidCastException
     */
    private function checkForInvalidOptions(array $arrayToValidate): void
    {
        $shipmentOptions = $arrayToValidate['options'];

        $validationResult = [
            'invalidOrder' => false,
            'options'      => [],
        ];

        foreach ($shipmentOptions as $shipmentOption => $values) {
            if ($shipmentOption === 'labelDescription') {
                continue;
            }

            $shipmentOptionsFromOrder = $this->order->deliveryOptions->shipmentOptions->toArray();

            if (is_bool($shipmentOptionsFromOrder[$shipmentOption])) {
                $shipmentOptionsFromOrder[$shipmentOption] = (int) $shipmentOptionsFromOrder[$shipmentOption];
            }

            if (array_key_exists($shipmentOption, $shipmentOptionsFromOrder)
                && ! in_array(
                    $shipmentOptionsFromOrder[$shipmentOption],
                    $values,
                    true
                )) {
                $validationResult['invalidOrder'] = true;
                $validationResult['options'][]    = [
                    'name'  => $shipmentOption,
                    'value' => $shipmentOptionsFromOrder[$shipmentOption],
                ];
            }
        }

        if ($validationResult['invalidOrder']) {
            $this->throwValidationError($validationResult);
        }
    }

    /**
     * @return array
     */
    private function findValidationArray(): array
    {
        $orderAttributes = [
            'platform'     => $this->order->platform,
            'shippingZone' => $this->getShippingZone(),
            'carrier'      => $this->order->deliveryOptions->carrier,
            'packageType'  => $this->order->deliveryOptions->packageType,
            'deliveryType' => $this->order->deliveryOptions->deliveryType,
        ];

        foreach ($orderAttributes as $orderAttribute) {
            if (null === $orderAttribute) {
                throw new RuntimeException(
                    sprintf('Order validation failed. %s can\'t be empty.', $orderAttribute)
                );
            }
        }

        $carrierIndex = array_search(
            $orderAttributes['carrier'],
            array_column($this->validationSchema['carriers'], 'name'),
            true
        );

        $carrier = $this->validationSchema['carriers'][$carrierIndex];

        $countryIndex = array_search(
            $orderAttributes['shippingZone'],
            array_column($carrier['shippingZone'], 'cc'),
            true
        );

        $country = $carrier['shippingZone'][$countryIndex];

        $packageTypeIndex = array_search(
            $orderAttributes['packageType'],
            array_column($country['packageTypes'], 'name'),
            true
        );

        $packageType = $country['packageTypes'][$packageTypeIndex];

        $deliveryTypeIndex = array_search(
            $orderAttributes['deliveryType'],
            array_column($packageType['deliveryTypes'], 'name'),
            true
        );

        return $packageType['deliveryTypes'][$deliveryTypeIndex];
    }

    private function getShippingZone(): string
    {
        $cc = $this->order->recipient->cc;

        if ($cc === CountryCodes::CC_NL) {
            return CountryCodes::CC_NL;
        }

        if ($cc === CountryCodes::CC_BE) {
            return CountryCodes::CC_BE;
        }

        if (in_array($cc, ValidationSchema::EU_COUNTRIES, true)) {
            return CountryCodes::ZONE_EU;
        }

        return CountryCodes::ZONE_ROW;
    }

    private function throwValidationError(array $validationResult): void
    {
        $options = [];
        $values  = [];

        foreach ($validationResult['options'] as $option) {
            $options[] = $option['name'];
            $values[]  = $option['value'];
        }
        throw new RuntimeException(
            sprintf(
                'Order can\'t have option(s) \'%s\' with value(s) \'%s\'',
                implode(', ', $options),
                implode(', ', $values)
            )
        );
    }
}
