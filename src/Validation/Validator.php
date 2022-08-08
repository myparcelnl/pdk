<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation;

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
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

    public function getOptions()
    {
        $arr = ValidationSchema::VALIDATION_SCHEMA_TWO;
        // hierarchy = country -> packagetype -> carrier -> options -> deliverytype (morning, standard, evening, pickup)
        // get the already defined options from the order
        $cc              = $this->order->recipient->getCountry();
        $deliveryOptions = $this->order->deliveryOptions;
        $packageType     = $deliveryOptions->packageType;
        $deliveryType    = $deliveryOptions->deliveryType;
        $carrier         = $deliveryOptions->carrier; // string myparcel carrier name for now
        $options         = $deliveryOptions->shipmentOptions;
        $weight          = $this->order->physicalProperties->weight;
        $hierarchyLevel  = 'cc';
        // pay attention to weight requirements...
        // use capabilities for total possible options

        // find the option arrays, and flatten these to include all options
        if ($carrier) {
            $capabilities   = (new CarrierOptions(['name' => $carrier]))->toArray();
            $hierarchyLevel = 'carrier';
        }
        // all higher hierarchies than hierarchyLevel return all options
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

        $this->checkForInvalidOptions($arrayToValidate['validationScheme']);

        $this->validateRequirements($arrayToValidate['requirements']);
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
     * @param $requirements
     *
     * @return void
     */
    private function validateRequirements($requirements): void
    {
        if ($requirements['weight']) {
            $this->validateWeight($requirements['weight']);
        }

        if ($requirements['labelDescription']) {
            $this->validateLabelDescription($requirements['labelDescription']);
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

        return [
            'validationScheme' => $packageType['deliveryTypes'][$deliveryTypeIndex],
            'requirements'     => $packageType['requirements'],
        ];
    }

    /**
     * @return string
     */
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

    /**
     * @param  array $validationResult
     *
     * @return void
     */
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
                'Order can\'t have option(s) \'%s\' with value(s) \'%s\'.',
                implode(', ', $options),
                implode(', ', $values)
            )
        );
    }

    /**
     * @param $labelDescriptionRequirements
     *
     * @return void
     */
    private function validateLabelDescription($labelDescriptionRequirements): void
    {
        $labelDescription = $this->order->deliveryOptions->shipmentOptions->labelDescription;
        $maximumLength    = $labelDescriptionRequirements['maxLength'];

        if (strlen((string) $labelDescription) > $maximumLength) {
            throw new RuntimeException(
                sprintf(
                    'Label description exceeds maximum amount of characters of %s.',
                    $maximumLength
                )
            );
        }
    }

    /**
     * @param $weightRequirements
     *
     * @return void
     */
    private function validateWeight($weightRequirements): void
    {
        $weight        = $this->order->physicalProperties->weight;
        $minimumWeight = $weightRequirements['minimum'];
        $maximumWeight = $weightRequirements['maximum'];

        if ($weight < $minimumWeight) {
            throw new RuntimeException(
                sprintf(
                    'Weight of %s doesn\'t meet requirements for the minimum weight of %s.',
                    $weight,
                    $minimumWeight
                )
            );
        }

        if ($weight > $maximumWeight) {
            throw new RuntimeException(
                sprintf(
                    'Weight of %s exceeds the maximum weight of %s',
                    $weight,
                    $maximumWeight
                )
            );
        }
    }
}
