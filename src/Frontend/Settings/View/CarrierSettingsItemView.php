<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\PlainElement;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CarrierSettingsItemView extends AbstractSettingsView
{
    /**
     * @var \MyParcelNL\Pdk\Carrier\Model\CarrierOptions
     */
    protected $carrierOptions;

    /**
     * @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema
     */
    protected $carrierSchema;

    /**
     * @var array
     */
    private $elements = [];

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\CarrierOptions $carrierOptions
     */
    public function __construct(CarrierOptions $carrierOptions)
    {
        $this->carrierOptions = $carrierOptions;

        /** @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $schema */
        $schema = Pdk::get(CarrierSchema::class);
        $schema->setCarrierOptions($carrierOptions);

        $this->carrierSchema = $schema;
    }

    /**
     * @return array
     */
    public function getDateFields(): array
    {
        return [
            new InteractiveElement(
                CarrierSettings::SHOW_DELIVERY_DAY,
                Components::INPUT_TOGGLE,
                ['$visibleWhen' => [CarrierSettings::ALLOW_DELIVERY_OPTIONS => true]]
            ),
            // todo make custom element for drop-off
            new InteractiveElement(
                CarrierSettings::DROP_OFF_POSSIBILITIES,
                Components::INPUT_DROP_OFF,
                ['$visibleWhen' => [CarrierSettings::ALLOW_DELIVERY_OPTIONS => true]]
            ),
        ];
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->createLabel('view', $this->getSettingsId(), 'description');
    }

    /**
     * @return array
     */
    public function getEveningDeliveryFields(): array
    {
        return [
            new InteractiveElement(
                CarrierSettings::ALLOW_EVENING_DELIVERY, Components::INPUT_TOGGLE,
                ['$visibleWhen' => [CarrierSettings::ALLOW_DELIVERY_OPTIONS => true]]
            ),
            new InteractiveElement(
                CarrierSettings::PRICE_DELIVERY_TYPE_EVENING,
                Components::INPUT_CURRENCY,
                [
                    '$visibleWhen' => [
                        CarrierSettings::ALLOW_DELIVERY_OPTIONS => true,
                        CarrierSettings::ALLOW_EVENING_DELIVERY => true,
                    ],
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    public function getMorningDeliveryFields(): array
    {
        return [
            new InteractiveElement(
                CarrierSettings::ALLOW_MORNING_DELIVERY, Components::INPUT_TOGGLE,
                ['$visibleWhen' => [CarrierSettings::ALLOW_DELIVERY_OPTIONS => true]]
            ),

            new InteractiveElement(
                CarrierSettings::PRICE_DELIVERY_TYPE_MORNING,
                Components::INPUT_CURRENCY,
                [
                    '$visibleWhen' => [
                        CarrierSettings::ALLOW_DELIVERY_OPTIONS => true,
                        CarrierSettings::ALLOW_MORNING_DELIVERY => true,
                    ],
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return "carrier_{$this->getFormattedCarrierName()}";
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function getElements(): FormElementCollection
    {
        if (empty($this->elements)) {
            $this->elements = $this->gatherElements();
        }

        return new FormElementCollection($this->elements);
    }

    protected function getLabelPrefix(): string
    {
        return CarrierSettings::ID;
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return sprintf('%s_%s', CarrierSettings::ID, $this->getFormattedCarrierName());
    }

    private function createDeliveryOptionsField(
        string $name,
        string $component,
        array  $options = []
    ): InteractiveElement {
        return new InteractiveElement(
            $name,
            $component,
            array_replace_recursive([
                '$visibleWhen' => [
                    CarrierSettings::ALLOW_DELIVERY_OPTIONS => true,
                ],
            ], $options)
        );
    }

    /**
     * @return void
     */
    private function gatherElements(): array
    {
        return Arr::flatten([
            /**
             * Default exports
             */
            [new PlainElement('Heading', ['$slot' => 'settings_carrier_export'])],

            $this->carrierSchema->canHaveInsurance() ? [
                new InteractiveElement(CarrierSettings::EXPORT_INSURANCE, Components::INPUT_TOGGLE),
                new InteractiveElement(
                    CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT,
                    Components::INPUT_NUMBER,
                    ['$visibleWhen' => [CarrierSettings::EXPORT_INSURANCE => true]]
                ),
                $this->getInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO, CountryCodes::CC_NL),
                $this->getInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO_BE, CountryCodes::CC_BE),
                $this->getInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO_EU, CountryCodes::CC_FR),
            ] : [],

            $this->carrierSchema->canHaveAgeCheck() ? [
                new InteractiveElement(CarrierSettings::EXPORT_AGE_CHECK, Components::INPUT_TOGGLE),
            ] : [],

            $this->carrierSchema->canHaveOnlyRecipient() ? [
                new InteractiveElement(CarrierSettings::EXPORT_ONLY_RECIPIENT, Components::INPUT_TOGGLE),
            ] : [],

            $this->carrierSchema->canHaveSignature() ? [
                new InteractiveElement(CarrierSettings::EXPORT_SIGNATURE, Components::INPUT_TOGGLE),
            ] : [],

            $this->carrierSchema->canHaveLargeFormat() ? [
                new InteractiveElement(CarrierSettings::EXPORT_LARGE_FORMAT, Components::INPUT_TOGGLE),
            ] : [],

            [new InteractiveElement(CarrierSettings::EXPORT_RETURN, Components::INPUT_TOGGLE)],

            /**
             * Delivery Options
             */
            [
                new PlainElement('Heading', ['$slot' => 'settings_carrier_delivery_options']),

                new InteractiveElement(CarrierSettings::ALLOW_DELIVERY_OPTIONS, Components::INPUT_TOGGLE),

                $this->createDeliveryOptionsField(CarrierSettings::ALLOW_MONDAY_DELIVERY, Components::INPUT_TOGGLE),
                $this->createDeliveryOptionsField(CarrierSettings::ALLOW_SATURDAY_DELIVERY, Components::INPUT_TOGGLE),
            ],

            $this->getPackageTypeFields(),

            $this->carrierSchema->canHavePickup() ? $this->getPickupFields() : [],
            $this->carrierSchema->canHaveSignature() ? $this->getSignatureFields() : [],
            $this->carrierSchema->canHaveOnlyRecipient() ? $this->getOnlyRecipientFields() : [],
            $this->carrierSchema->canHaveMorningDelivery() ? $this->getMorningDeliveryFields() : [],
            $this->carrierSchema->canHaveEveningDelivery() ? $this->getEveningDeliveryFields() : [],
            $this->carrierSchema->canHaveSameDayDelivery() ? $this->getSameDayDeliveryFields() : [],
            $this->carrierSchema->canHaveDate() ? $this->getDateFields() : [],
        ], 1);
    }

    /**
     * @return string
     */
    private function getFormattedCarrierName(): string
    {
        return Str::snake(str_replace('.', '_', $this->carrierOptions->carrier->name));
    }

    /**
     * @param  string $name
     * @param  string $cc
     *
     * @return null|\MyParcelNL\Pdk\Frontend\Form\InteractiveElement
     */
    private function getInsuranceElement(string $name, string $cc): ?InteractiveElement
    {
        $insuranceAmounts = $this->carrierSchema->getAllowedInsuranceAmounts();

        if (count($insuranceAmounts)) {
            $options = array_map(static function ($option) {
                return $option / 100;
            }, $insuranceAmounts);

            return new InteractiveElement(
                $name,
                Components::INPUT_SELECT,
                [
                    'options' => $this->toSelectOptions($options),
                    '$visibleWhen' => [
                        CarrierSettings::EXPORT_INSURANCE => true,
                    ],
                ]
            );
        }

        return null;
    }

    private function getOnlyRecipientFields(): array
    {
        return [
            $this->createDeliveryOptionsField(
                CarrierSettings::ALLOW_ONLY_RECIPIENT,
                Components::INPUT_TOGGLE
            ),

            $this->createDeliveryOptionsField(
                CarrierSettings::PRICE_ONLY_RECIPIENT,
                Components::INPUT_CURRENCY,
                ['$visibleWhen' => [CarrierSettings::ALLOW_ONLY_RECIPIENT => true]]
            ),
        ];
    }

    private function getPackageTypeFields(): array
    {
        $allowedPackageTypes = $this->carrierSchema->getAllowedPackageTypes();
        $fields              = [
            $this->createDeliveryOptionsField(CarrierSettings::DEFAULT_PACKAGE_TYPE, Components::INPUT_SELECT, [
                'options' => $allowedPackageTypes,
            ]),
        ];

        if (in_array(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME, $allowedPackageTypes, true)) {
            $fields[] = $this->createDeliveryOptionsField(
                CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
                Components::INPUT_CURRENCY
            );
        }

        if (in_array(DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME, $allowedPackageTypes, true)) {
            $fields[] = $this->createDeliveryOptionsField(
                CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
                Components::INPUT_CURRENCY
            );
        }

        return $fields;
    }

    /**
     * @return array
     */
    private function getPickupFields(): array
    {
        return [
            $this->createDeliveryOptionsField(
                CarrierSettings::ALLOW_PICKUP_LOCATIONS,
                Components::INPUT_TOGGLE
            ),
            $this->createDeliveryOptionsField(
                CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP,
                Components::INPUT_CURRENCY,
                ['$visibleWhen' => [CarrierSettings::ALLOW_PICKUP_LOCATIONS => true]]
            ),
        ];
    }

    /**
     * @return array
     */
    private function getSameDayDeliveryFields(): array
    {
        return [
            $this->createDeliveryOptionsField(
                CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
                Components::INPUT_TOGGLE
            ),
            $this->createDeliveryOptionsField(
                CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY,
                Components::INPUT_CURRENCY,
                ['$visibleWhen' => [CarrierSettings::ALLOW_SAME_DAY_DELIVERY => true]]
            ),
        ];
    }

    /**
     * @return array
     */
    private function getSignatureFields(): array
    {
        return [
            $this->createDeliveryOptionsField(
                CarrierSettings::ALLOW_SIGNATURE,
                Components::INPUT_TOGGLE
            ),
            $this->createDeliveryOptionsField(
                CarrierSettings::PRICE_SIGNATURE,
                Components::INPUT_CURRENCY,
                ['$visibleWhen' => [CarrierSettings::ALLOW_SIGNATURE => true]]
            ),
        ];
    }
}
