<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
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
     * @var \MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    protected $carrier;

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
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     */
    public function __construct(Carrier $carrier)
    {
        $this->currencyService = Pdk::get(CurrencyServiceInterface::class);
        $this->carrier         = $carrier;

        /** @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $schema */
        $schema = Pdk::get(CarrierSchema::class);
        $schema->setCarrier($carrier);

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
     * @return null|string
     */
    public function getTitleSuffix(): ?string
    {
        if ($this->carrier->isDefault) {
            return null;
        }

        return $this->createLabel('carrier_type', $this->carrier->type);
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

    /**
     * @return string
     */
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

    /**
     * @param  string $string
     *
     * @return string
     */
    private function createGenericLabel(string $string): string
    {
        return $this->createLabel($this->getLabelPrefix(), $string);
    }

    /**
     * @param  string $name
     *
     * @return null|\MyParcelNL\Pdk\Frontend\Form\InteractiveElement
     */
    private function createInsuranceElement(string $name): ?InteractiveElement
    {
        $insuranceAmounts = $this->carrierSchema->getAllowedInsuranceAmounts();

        if (! count($insuranceAmounts)) {
            return null;
        }

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

    /**
     * @param  string $allowSetting
     * @param  string $priceSetting
     *
     * @return array
     */
    private function createSettingWithPriceFields(
        string $allowSetting,
        string $priceSetting
    ): array {
        return [
            new InteractiveElement($allowSetting, Components::INPUT_TOGGLE),
            new InteractiveElement(
                $priceSetting,
                Components::INPUT_CURRENCY,
                [
                    '$visibleWhen' => [
                        $allowSetting => true,
                    ],
                ]
            ),
        ];
    }

    /**
     * @return void
     */
    private function gatherElements(): array
    {
        return $this->flattenElements([
            /**
             * Default export settings.
             */
            $this->getDefaultExportFields(),

            /**
             * Default export settings for returns.
             */
            $this->getDefaultExportReturnsFields(),

            /**
             * Delivery options settings.
             */
            $this->getDeliveryOptionsFields(),
        ]);
    }

    /**
     * @return array
     */
    private function getDefaultExportFields(): array
    {
        return [
            /**
             * Export settings for regular shipments.
             */
            new SettingsDivider($this->createGenericLabel('export')),

            $this->carrierSchema->canHaveSignature() ? [
                new InteractiveElement(CarrierSettings::EXPORT_SIGNATURE, Components::INPUT_TOGGLE),
            ] : [],

            $this->carrierSchema->canHaveOnlyRecipient() ? [
                new InteractiveElement(CarrierSettings::EXPORT_ONLY_RECIPIENT, Components::INPUT_TOGGLE),
            ] : [],

            $this->carrierSchema->canHaveAgeCheck() ? [
                new InteractiveElement(CarrierSettings::EXPORT_AGE_CHECK, Components::INPUT_TOGGLE),
            ] : [],

            $this->carrierSchema->canHaveLargeFormat() ? [
                new InteractiveElement(CarrierSettings::EXPORT_LARGE_FORMAT, Components::INPUT_TOGGLE),
            ] : [],

            $this->carrierSchema->canHaveDirectReturn() ? [
                new InteractiveElement(CarrierSettings::EXPORT_RETURN, Components::INPUT_TOGGLE),
            ] : [],

            $this->carrierSchema->canHaveInsurance() ? $this->getExportInsuranceFields() : [],
        ];
    }

    /**
     * @return array
     */
    private function getDefaultExportReturnsFields(): array
    {
        $hasPackageTypeOptions = count($this->carrierSchema->getAllowedPackageTypes()) > 1;
        $canHaveLargeFormat    = $this->carrierSchema->canHaveLargeFormat();

        if (! $hasPackageTypeOptions && ! $canHaveLargeFormat) {
            return [];
        }

        $fields = [new SettingsDivider($this->createGenericLabel('export_returns'))];

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
    private function getDeliveryOptionsFields(): array
    {
        return [
            new SettingsDivider($this->createGenericLabel('delivery_options')),
            new InteractiveElement(CarrierSettings::DELIVERY_OPTIONS_ENABLED, Components::INPUT_TOGGLE),

            /**
             * Home delivery
             */
            $this->withProps(
                [
                    '$visibleWhen' => [
                        CarrierSettings::DELIVERY_OPTIONS_ENABLED => true,
                    ],
                ],
                new SettingsDivider($this->createGenericLabel('delivery_options_delivery'), SettingsDivider::LEVEL_3),
                new InteractiveElement(CarrierSettings::ALLOW_DELIVERY_OPTIONS, Components::INPUT_TOGGLE)
            ),

            $this->withProps(
                [
                    '$visibleWhen' => [
                        CarrierSettings::DELIVERY_OPTIONS_ENABLED => true,
                        CarrierSettings::ALLOW_DELIVERY_OPTIONS   => true,
                    ],
                ],

                $this->getPackageTypeFields(),

                new InteractiveElement(
                    CarrierSettings::DELIVERY_DAYS_WINDOW,
                    Components::INPUT_NUMBER,
                    [
                        '$attributes' => [
                            'min' => Pdk::get('deliveryDaysWindowMin'),
                            'max' => Pdk::get('deliveryDaysWindowMax'),
                        ],
                    ]
                ),

                new InteractiveElement(
                    CarrierSettings::DROP_OFF_DELAY,
                    Components::INPUT_NUMBER
                ),

                new InteractiveElement(
                    CarrierSettings::DROP_OFF_POSSIBILITIES,
                    Components::INPUT_DROP_OFF
                ),

                new SettingsDivider($this->createGenericLabel('delivery_moments'), SettingsDivider::LEVEL_4),

                $this->carrierSchema->canHaveDate() ? [
                    new InteractiveElement(
                        CarrierSettings::SHOW_DELIVERY_DAY,
                        Components::INPUT_TOGGLE
                    ),
                ] : [],

                $this->createSettingWithPriceFields(
                    CarrierSettings::ALLOW_MONDAY_DELIVERY,
                    CarrierSettings::PRICE_DELIVERY_TYPE_MONDAY
                ),

                $this->createSettingWithPriceFields(
                    CarrierSettings::ALLOW_SATURDAY_DELIVERY,
                    CarrierSettings::PRICE_DELIVERY_TYPE_SATURDAY
                ),

                $this->carrierSchema->canHaveMorningDelivery() ? $this->createSettingWithPriceFields(
                    CarrierSettings::ALLOW_MORNING_DELIVERY,
                    CarrierSettings::PRICE_DELIVERY_TYPE_MORNING
                ) : [],

                $this->carrierSchema->canHaveEveningDelivery() ? $this->createSettingWithPriceFields(
                    CarrierSettings::ALLOW_EVENING_DELIVERY,
                    CarrierSettings::PRICE_DELIVERY_TYPE_EVENING
                ) : [],

                $this->carrierSchema->canHaveSameDayDelivery() ? array_merge(
                    $this->createSettingWithPriceFields(
                        CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
                        CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY
                    ),
                    [
                        new InteractiveElement(CarrierSettings::CUTOFF_TIME_SAME_DAY, Components::INPUT_TIME),
                    ]
                ) : [],

                new SettingsDivider($this->createGenericLabel('shipment_options'), SettingsDivider::LEVEL_4),

                $this->carrierSchema->canHaveSignature() ? $this->createSettingWithPriceFields(
                    CarrierSettings::ALLOW_SIGNATURE,
                    CarrierSettings::PRICE_SIGNATURE
                ) : [],

                $this->carrierSchema->canHaveOnlyRecipient() ? $this->createSettingWithPriceFields(
                    CarrierSettings::ALLOW_ONLY_RECIPIENT,
                    CarrierSettings::PRICE_ONLY_RECIPIENT
                ) : []
            ),

            /**
             * Pickup locations
             */
            $this->carrierSchema->canHavePickup() ? $this->withProps(
                [
                    '$visibleWhen' => [
                        CarrierSettings::DELIVERY_OPTIONS_ENABLED => true,
                    ],
                ],
                new SettingsDivider($this->createGenericLabel('delivery_options_pickup'), SettingsDivider::LEVEL_3),
                $this->createSettingWithPriceFields(
                    CarrierSettings::ALLOW_PICKUP_LOCATIONS,
                    CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP
                )
            ) : [],
        ];
    }

    /**
     * @return array
     */
    private function getExportInsuranceFields(): array
    {
        return [
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
        ];
    }

    /**
     * @return string
     */
    private function getFormattedCarrierName(): string
    {
        return Str::snake(str_replace('.', '_', $this->carrier->name));
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Form\InteractiveElement[]
     */
    private function getPackageTypeFields(): array
    {
        $allowedPackageTypes = $this->carrierSchema->getAllowedPackageTypes();

        $fields = [
            new InteractiveElement(CarrierSettings::DEFAULT_PACKAGE_TYPE, Components::INPUT_SELECT, [
                'options' => $this->createPackageTypeOptions($allowedPackageTypes),
            ]),
            new InteractiveElement(CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD, Components::INPUT_CURRENCY),
        ];

        if (in_array(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME, $allowedPackageTypes, true)) {
            $fields[] = new InteractiveElement(
                CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
                Components::INPUT_CURRENCY
            );
        }

        if (in_array(DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME, $allowedPackageTypes, true)) {
            $fields[] = new InteractiveElement(
                CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
                Components::INPUT_CURRENCY
            );
        }

        return $fields;
    }
}
