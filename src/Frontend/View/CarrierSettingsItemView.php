<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\SettingsDivider;
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
     * @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface
     */
    private $currencyService;

    /**
     * @var array
     */
    private $elements = [];

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\CarrierOptions $carrierOptions
     */
    public function __construct(CarrierOptions $carrierOptions)
    {
        $this->currencyService = Pdk::get(CurrencyServiceInterface::class);
        $this->carrierOptions  = $carrierOptions;

        /** @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $schema */
        $schema = Pdk::get(CarrierSchema::class);
        $schema->setCarrierOptions($carrierOptions);

        $this->carrierSchema = $schema;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->createLabel('view', $this->getSettingsId(), 'description');
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
    protected function createElements(): FormElementCollection
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
     * @param  string $name
     *
     * @return null|\MyParcelNL\Pdk\Frontend\Form\InteractiveElement
     */
    private function createInsuranceElement(string $name): ?InteractiveElement
    {
        $insuranceAmounts = $this->carrierSchema->getAllowedInsuranceAmounts();

        if (count($insuranceAmounts)) {
            $options = array_map(function (int $amount) {
                return $this->currencyService->format($amount);
            }, array_combine($insuranceAmounts, $insuranceAmounts));

            return new InteractiveElement(
                $name,
                Components::INPUT_SELECT,
                [
                    '$visibleWhen' => [CarrierSettings::EXPORT_INSURANCE => true],
                    'options'      => $this->toSelectOptions($options, false, true),
                ]
            );
        }

        return null;
    }

    /**
     * @return void
     */
    private function gatherElements(): array
    {
        return Arr::flatten([
            /**
             * Export settings for regular shipments.
             */
            [new SettingsDivider('settings_carrier_export')],

            $this->carrierSchema->canHaveInsurance() ? [
                new InteractiveElement(CarrierSettings::EXPORT_INSURANCE, Components::INPUT_TOGGLE),
                new InteractiveElement(
                    CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT,
                    Components::INPUT_NUMBER,
                    ['$visibleWhen' => [CarrierSettings::EXPORT_INSURANCE => true]]
                ),
                $this->createInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO),
                $this->createInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE),
                $this->createInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO_EU),
                $this->createInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW),
                new InteractiveElement(
                    CarrierSettings::EXPORT_INSURANCE_PRICE_FACTOR,
                    Components::INPUT_NUMBER,
                    [
                        '$visibleWhen' => [CarrierSettings::EXPORT_INSURANCE => true],
                        '$attributes'  => [
                            'min' => Pdk::get('insuranceFactorMin'),
                            'step' => Pdk::get('insuranceFactorStep'),
                            'max' => Pdk::get('insuranceFactorMax'),
                        ],
                    ]
                ),
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

            $this->carrierSchema->canHaveDirectReturn() ? [
                new InteractiveElement(CarrierSettings::EXPORT_RETURN, Components::INPUT_TOGGLE),
            ] : [],

            /**
             * Export settings for return shipments.
             */
            $this->getReturnsFields(),

            /**
             * Delivery Options
             */
            [new SettingsDivider('settings_carrier_delivery_options')],

            [
                new InteractiveElement(CarrierSettings::ALLOW_DELIVERY_OPTIONS, Components::INPUT_TOGGLE),
                $this->createDeliveryOptionsField(CarrierSettings::DELIVERY_DAYS_WINDOW, Components::INPUT_NUMBER, [
                    '$attributes' => [
                        'min' => Pdk::get('deliveryDaysWindowMin'),
                        'max' => Pdk::get('deliveryDaysWindowMax'),
                    ],
                ]),
                $this->createDeliveryOptionsField(CarrierSettings::DROP_OFF_DELAY, Components::INPUT_NUMBER),
            ],

            $this->createDeliveryOptionsField(
                CarrierSettings::DROP_OFF_POSSIBILITIES,
                Components::INPUT_DROP_OFF
            ),

            $this->carrierSchema->canHaveDate() ? [
                $this->createDeliveryOptionsField(
                    CarrierSettings::SHOW_DELIVERY_DAY,
                    Components::INPUT_TOGGLE
                ),
            ] : [],

            $this->getPackageTypeFields(),

            [
                new SettingsDivider(
                    'settings_carrier_delivery_moments',
                    null,
                    ['$visibleWhen' => [CarrierSettings::ALLOW_DELIVERY_OPTIONS => true]]
                ),
            ],

            [$this->createDeliveryOptionsField(CarrierSettings::ALLOW_MONDAY_DELIVERY, Components::INPUT_TOGGLE)],
            [$this->createDeliveryOptionsField(CarrierSettings::ALLOW_SATURDAY_DELIVERY, Components::INPUT_TOGGLE)],
            $this->carrierSchema->canHaveMorningDelivery() ? $this->getMorningDeliveryFields() : [],
            $this->carrierSchema->canHaveEveningDelivery() ? $this->getEveningDeliveryFields() : [],
            $this->carrierSchema->canHaveSameDayDelivery() ? $this->getSameDayDeliveryFields() : [],

            $this->carrierSchema->canHavePickup() ? $this->getPickupFields() : [],

            [
                new SettingsDivider(
                    'settings_carrier_shipment_options',
                    null,
                    ['$visibleWhen' => [CarrierSettings::ALLOW_DELIVERY_OPTIONS => true]]
                ),
            ],

            $this->carrierSchema->canHaveSignature() ? $this->getSignatureFields() : [],
            $this->carrierSchema->canHaveOnlyRecipient() ? $this->getOnlyRecipientFields() : [],
        ], 1);
    }

    /**
     * @return array
     */
    private function getEveningDeliveryFields(): array
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
     * @return string
     */
    private function getFormattedCarrierName(): string
    {
        return Str::snake(str_replace('.', '_', $this->carrierOptions->carrier->name));
    }

    /**
     * @return array
     */
    private function getMorningDeliveryFields(): array
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

        $fields = [
            $this->createDeliveryOptionsField(CarrierSettings::DEFAULT_PACKAGE_TYPE, Components::INPUT_SELECT, [
                'options' => $this->createPackageTypeOptions($allowedPackageTypes),
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
    private function getReturnsFields(): array
    {
        $hasPackageTypeOptions = count($this->carrierSchema->getAllowedPackageTypes()) > 1;
        $canHaveLargeFormat    = $this->carrierSchema->canHaveLargeFormat();

        if (! $hasPackageTypeOptions && ! $canHaveLargeFormat) {
            return [];
        }

        $fields = [new SettingsDivider('settings_carrier_export_returns')];

        if ($hasPackageTypeOptions) {
            $fields[] = new InteractiveElement(
                CarrierSettings::EXPORT_RETURN_PACKAGE_TYPE,
                Components::INPUT_SELECT,
                [
                    'options' => $this->createPackageTypeOptions($this->carrierSchema->getAllowedPackageTypes()),
                ]
            );
        }

        if ($canHaveLargeFormat) {
            $fields[] = new InteractiveElement(
                CarrierSettings::EXPORT_RETURN_LARGE_FORMAT,
                Components::INPUT_TOGGLE
            );
        }

        return $fields;
    }

    /**
     * @return array
     */
    private function getSameDayDeliveryFields(): array
    {
        $sameDayEnabled = ['$visibleWhen' => [CarrierSettings::ALLOW_SAME_DAY_DELIVERY => true]];

        return [
            $this->createDeliveryOptionsField(
                CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
                Components::INPUT_TOGGLE
            ),
            $this->createDeliveryOptionsField(
                CarrierSettings::CUTOFF_TIME_SAME_DAY,
                Components::INPUT_TIME,
                $sameDayEnabled
            ),
            $this->createDeliveryOptionsField(
                CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY,
                Components::INPUT_CURRENCY,
                $sameDayEnabled
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
