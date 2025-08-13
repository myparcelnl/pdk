<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormAfterUpdateBuilder;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\SettingsDivider;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
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
     * @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface
     */
    private $currencyService;

    /**
     * @var \MyParcelNL\Pdk\Proposition\Service\PropositionService
     */
    protected $propositionService;

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
        $this->propositionService = Pdk::get(PropositionService::class);
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

        return sprintf('%s_type_%s', $this->getLabelPrefix(), $this->carrier->type);
    }

    /**
     * @return null|array
     */
    protected function createElements(): ?array
    {
        if (empty($this->elements)) {
            $this->elements = $this->gatherElements();
        }

        return $this->elements;
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
     * @return \MyParcelNL\Pdk\Frontend\Form\InteractiveElement
     */
    private function createInsuranceElement(string $name): InteractiveElement
    {
        $hasInsurance = in_array(PropositionCarrierFeatures::SHIPMENT_OPTION_INSURANCE_NAME, $this->carrier->outboundFeatures['shipmentOptions']);

        if ($hasInsurance) {
            $insuranceAmounts = [0, 1000, 2000]; // @todo
        }

        $options = array_map(function (int $amount) {
            return $this->currencyService->format($amount);
        }, array_combine($insuranceAmounts, $insuranceAmounts));

        return new InteractiveElement(
            $name,
            Components::INPUT_SELECT,
            ['options' => $this->toSelectOptions($options, AbstractSettingsView::SELECT_USE_PLAIN_LABEL)]
        );
    }

    /**
     * @return array
     */
    private function createInternationalMailboxFields(): array
    {
        if (! AccountSettings::hasCarrierSmallPackageContract()
            || ! $this->propositionService->carrierHasMetadataFeature($this->carrier, 'carrierSmallpackageContract')) {
            return [];
        }

        return $this->createSettingWithPriceFields(
            CarrierSettings::ALLOW_INTERNATIONAL_MAILBOX,
            CarrierSettings::PRICE_INTERNATIONAL_MAILBOX
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
            (new InteractiveElement($priceSetting, Components::INPUT_CURRENCY))
                ->builder(function (FormOperationBuilder $builder) use ($allowSetting) {
                    $builder->visibleWhen($allowSetting);
                }),
        ];
    }

    /**
     * @return array
     */
    private function gatherElements(): array
    {
        return [
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
        ];
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

            array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_AGE_CHECK_NAME, $this->carrier->outboundFeatures['shipmentOptions'] ?? [])
                ? [
                (new InteractiveElement(CarrierSettings::EXPORT_AGE_CHECK, Components::INPUT_TOGGLE))
                    ->builder(function (FormOperationBuilder $builder) {
                        $builder->afterUpdate(function (FormAfterUpdateBuilder $afterUpdate) {
                            $afterUpdate
                                ->setValue(true)
                                ->on(CarrierSettings::EXPORT_SIGNATURE)
                                ->if->eq(true);

                            $afterUpdate
                                ->setValue(true)
                                ->on(CarrierSettings::EXPORT_ONLY_RECIPIENT)
                                ->if->eq(true);
                        });
                    }),
            ]
                : [],

            $this->withOperation(
                function (FormOperationBuilder $builder) {
                    if (array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_AGE_CHECK_NAME, $this->carrier->outboundFeatures['shipmentOptions'] ?? [])) {
                        return;
                    }

                    $builder->readOnlyWhen(CarrierSettings::EXPORT_AGE_CHECK);
                },
                array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_SIGNATURE_NAME, $this->carrier->outboundFeatures['shipmentOptions'] ?? [])
                    ? [new InteractiveElement(CarrierSettings::EXPORT_SIGNATURE, Components::INPUT_TOGGLE)]
                    : [],
                array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_ONLY_RECIPIENT_NAME, $this->carrier->outboundFeatures['shipmentOptions'] ?? [])
                    ? [new InteractiveElement(CarrierSettings::EXPORT_ONLY_RECIPIENT, Components::INPUT_TOGGLE)]
                    : []
            ),

            array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_RECEIPT_CODE_NAME, $this->carrier->outboundFeatures['shipmentOptions'] ?? []) ? [
                new InteractiveElement(CarrierSettings::EXPORT_RECEIPT_CODE, Components::INPUT_TOGGLE),
            ] : [],

            array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_LARGE_FORMAT_NAME, $this->carrier->outboundFeatures['shipmentOptions'] ?? [])
                ? [new InteractiveElement(CarrierSettings::EXPORT_LARGE_FORMAT, Components::INPUT_TOGGLE)]
                : [],

            array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_DIRECT_RETURN_NAME, $this->carrier->outboundFeatures['shipmentOptions'] ?? [])
                ? [new InteractiveElement(CarrierSettings::EXPORT_RETURN, Components::INPUT_TOGGLE)]
                : [],

            array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_HIDE_SENDER_NAME, $this->carrier->outboundFeatures['shipmentOptions'] ?? [])
                ? [new InteractiveElement(CarrierSettings::EXPORT_HIDE_SENDER, Components::INPUT_TOGGLE)]
                : [],

            array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_INSURANCE_NAME, $this->carrier->outboundFeatures['shipmentOptions'] ?? [])
                ? $this->getExportInsuranceFields()
                : [],

            array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_COLLECT_NAME, $this->carrier->outboundFeatures['shipmentOptions'] ?? [])
                ? [new InteractiveElement(CarrierSettings::EXPORT_COLLECT, Components::INPUT_TOGGLE)]
                : [],
        ];
    }

    /**
     * @return array
     */
    private function getDefaultExportReturnsFields(): array
    {
        $hasPackageTypeOptions = !empty($this->carrier->outboundFeatures->packageTypes);
        $canHaveLargeFormat    = array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_LARGE_FORMAT_NAME, $this->carrier->outboundFeatures['shipmentOptions'] ?? []);

        if (! $hasPackageTypeOptions && ! $canHaveLargeFormat) {
            return [];
        }

        $fields = [new SettingsDivider($this->createGenericLabel('export_returns'))];

        if ($hasPackageTypeOptions) {
            $fields[] = new InteractiveElement(
                CarrierSettings::EXPORT_RETURN_PACKAGE_TYPE,
                Components::INPUT_SELECT,
                [
                    'options' => $this->createPackageTypeOptions($this->carrier->outboundFeatures->packageTypes),
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
        $elements = [];

        // Main delivery options section
        $elements[] = new SettingsDivider($this->createGenericLabel('delivery_options'));
        $elements[] = new InteractiveElement(CarrierSettings::DELIVERY_OPTIONS_ENABLED, Components::INPUT_TOGGLE);

        // Home delivery section
        $elements[] = $this->withOperation(
            function (FormOperationBuilder $builder) {
                $builder->visibleWhen(CarrierSettings::DELIVERY_OPTIONS_ENABLED);
            },
            new SettingsDivider($this->createGenericLabel('delivery_options_delivery'), SettingsDivider::LEVEL_3),
            new InteractiveElement(CarrierSettings::ALLOW_DELIVERY_OPTIONS, Components::INPUT_TOGGLE)
        );

        // Delivery options configuration (when enabled)
        $deliveryOptionsConfig = [
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
            new InteractiveElement(CarrierSettings::DROP_OFF_DELAY, Components::INPUT_NUMBER),
            new InteractiveElement(CarrierSettings::DROP_OFF_POSSIBILITIES, Components::INPUT_DROP_OFF),
        ];

        // Add delivery moments section
        $deliveryOptionsConfig[] = new SettingsDivider($this->createGenericLabel('delivery_moments'), SettingsDivider::LEVEL_4);

        // Add same day delivery settings
        $deliveryOptionsConfig = array_merge($deliveryOptionsConfig, $this->getSameDayDeliverySettings());

        // Add dynamic delivery type settings
        $deliveryOptionsConfig = array_merge($deliveryOptionsConfig, $this->getDeliveryTypeSettings());

        // Add shipment options section
        $deliveryOptionsConfig[] = new SettingsDivider($this->createGenericLabel('shipment_options'), SettingsDivider::LEVEL_4);
        $deliveryOptionsConfig = array_merge($deliveryOptionsConfig, $this->getShipmentOptionsSettings());

        // Wrap delivery options config with visibility conditions
        $elements[] = $this->withOperation(
            function (FormOperationBuilder $builder) {
                $builder
                    ->visibleWhen(CarrierSettings::DELIVERY_OPTIONS_ENABLED)
                    ->and(CarrierSettings::ALLOW_DELIVERY_OPTIONS);
            },
            ...$deliveryOptionsConfig
        );

        return $elements;
    }

    /**
     * Get same day delivery settings
     */
    private function getSameDayDeliverySettings(): array
    {
        if (!array_key_exists(PropositionCarrierFeatures::DELIVERY_TYPE_SAME_DAY_NAME, $this->carrier->outboundFeatures['shipmentOptions'] ?? [])) {
            return [];
        }

        return array_merge(
            $this->createSettingWithPriceFields(
                CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
                CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY
            ),
            [new InteractiveElement(CarrierSettings::CUTOFF_TIME_SAME_DAY, Components::INPUT_TIME)]
        );
    }

    /**
     * Get dynamic delivery type settings based on carrier capabilities
     */
    private function getDeliveryTypeSettings(): array
    {
        $settings = [];

        if (!$this->carrier->outboundFeatures->deliveryTypes) {
            return $settings;
        }

        foreach ($this->carrier->outboundFeatures->deliveryTypes as $deliveryType) {
            // Convert new delivery type names to the ones used for delivery options
            $deliveryType = $this->propositionService->deliveryTypeNameForDeliveryOptions($deliveryType);

            // Ignore unsupported types
            if ($deliveryType === false) {
                continue;
            }

            $settings = array_merge(
                $settings,
                $this->createSettingWithPriceFields(
                    constant(CarrierSettings::class . "::ALLOW_" . strtoupper($deliveryType) . "_DELIVERY"),
                    constant(CarrierSettings::class . "::PRICE_DELIVERY_TYPE_" . strtoupper($deliveryType))
                )
            );

            // Handle pickup-specific settings
            if ($deliveryType === DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME) {
                $settings[] = $this->withOperation(
                    function (FormOperationBuilder $builder) {
                        $builder->visibleWhen(CarrierSettings::DELIVERY_OPTIONS_ENABLED);
                    },
                    new SettingsDivider($this->createGenericLabel('delivery_options_pickup'), SettingsDivider::LEVEL_3),
                    ...$this->createSettingWithPriceFields(
                        CarrierSettings::ALLOW_PICKUP_LOCATIONS,
                        CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP
                    )
                );
            }
        }

        return $settings;
    }

    /**
     * Get shipment options settings based on carrier capabilities
     */
    private function getShipmentOptionsSettings(): array
    {
        $settings = [];
        $shipmentOptions = $this->carrier->outboundFeatures['shipmentOptions'] ?? [];

        // Signature option
        if (array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_SIGNATURE_NAME, $shipmentOptions)) {
            $settings = array_merge(
                $settings,
                $this->createSettingWithPriceFields(
                    CarrierSettings::ALLOW_SIGNATURE,
                    CarrierSettings::PRICE_SIGNATURE
                )
            );
        }

        // Only recipient option
        if (array_key_exists(PropositionCarrierFeatures::SHIPMENT_OPTION_ONLY_RECIPIENT_NAME, $shipmentOptions)) {
            $settings = array_merge(
                $settings,
                $this->createSettingWithPriceFields(
                    CarrierSettings::ALLOW_ONLY_RECIPIENT,
                    CarrierSettings::PRICE_ONLY_RECIPIENT
                )
            );
        }

        return $settings;
    }

    /**
     * @return array
     */
    private function getExportInsuranceFields(): array
    {
        $insuranceAmounts = []; //@todo

        if (count($insuranceAmounts) <= 1) {
            return [];
        }

        return [
            new InteractiveElement(CarrierSettings::EXPORT_INSURANCE, Components::INPUT_TOGGLE),

            $this->withOperation(
                function (FormOperationBuilder $builder) {
                    $builder->visibleWhen(CarrierSettings::EXPORT_INSURANCE);
                },
                new InteractiveElement(CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT, Components::INPUT_NUMBER),
                $this->createInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO),
                $this->createInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE),
                $this->createInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO_EU),
                $this->createInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW),
                new InteractiveElement(
                    CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE,
                    Components::INPUT_NUMBER,
                    [
                        '$attributes' => [
                            'min' => Pdk::get('insurancePercentageMin'),
                            'step' => Pdk::get('insurancePercentageStep'),
                            'max' => Pdk::get('insurancePercentageMax'),
                        ],
                    ]
                )
            ),
        ];
    }

    /**
     * @return string
     */
    private function getFormattedCarrierName(): string
    {
        return Str::snake(str_replace('.', '_', strtolower($this->carrier->name)));
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Form\InteractiveElement[]
     */
    private function getPackageTypeFields(): array
    {
        $allowedPackageTypes = $this->carrier->outboundFeatures->packageTypes ?? [];

        $fields = [
            new InteractiveElement(CarrierSettings::DEFAULT_PACKAGE_TYPE, Components::INPUT_SELECT, [
                'options' => $this->createPackageTypeOptions($allowedPackageTypes),
            ]),
        ];

        if (in_array(PropositionCarrierFeatures::PACKAGE_TYPE_PACKAGE_SMALL_NAME, $allowedPackageTypes, true)) {
            $fields[] = new InteractiveElement(
                CarrierSettings::PRICE_PACKAGE_TYPE_PACKAGE_SMALL,
                Components::INPUT_CURRENCY
            );
        }

        if (in_array(PropositionCarrierFeatures::PACKAGE_TYPE_MAILBOX_NAME, $allowedPackageTypes, true)) {
            $fields[] = new InteractiveElement(
                CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
                Components::INPUT_CURRENCY
            );

            $fields[] = $this->createInternationalMailboxFields();
        }

        if (in_array(PropositionCarrierFeatures::PACKAGE_TYPE_DIGITAL_STAMP_NAME, $allowedPackageTypes, true)) {
            $fields[] = new InteractiveElement(
                CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
                Components::INPUT_CURRENCY
            );
        }

        return $fields;
    }
}
