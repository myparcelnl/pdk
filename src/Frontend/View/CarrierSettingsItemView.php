<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\App\Service\DeliveryOptionsResetService;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormAfterUpdateBuilder;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\SettingsDivider;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;
use MyParcelNL\Sdk\Support\Str;

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
     * @var \MyParcelNL\Pdk\Proposition\Service\PropositionService
     */
    protected $propositionService;

    /**
     * @var \MyParcelNL\Pdk\App\Service\DeliveryOptionsResetService
     */
    private $deliveryOptionsResetService;

    /**
     * @var array
     */
    private $elements = [];

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     */
    public function __construct(Carrier $carrier)
    {
        $this->currencyService             = Pdk::get(CurrencyServiceInterface::class);
        $this->deliveryOptionsResetService = Pdk::get(DeliveryOptionsResetService::class);
        $this->carrier                     = $carrier;
        $this->propositionService          = Pdk::get(PropositionService::class);


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
        $hasInsurance = $this->carrierSchema->canHaveInsurance();

        if ($hasInsurance) {
            $insuranceAmounts = $this->carrier->outboundFeatures['metadata']['insuranceOptions'] ?? [];
        } else {
            $insuranceAmounts = [];
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
        $fields = $this->createSettingWithPriceFields(
            CarrierSettings::ALLOW_INTERNATIONAL_MAILBOX,
            CarrierSettings::PRICE_INTERNATIONAL_MAILBOX
        );

        // Add tracked toggle for carriers with custom mailbox contract, only visible when international mailbox is enabled
        if ($this->carrierSchema->canHaveTracked() && AccountSettings::hasCarrierSmallPackageContract()) {
            $trackedElement = (new InteractiveElement(CarrierSettings::EXPORT_TRACKED, Components::INPUT_TOGGLE))
                ->builder(function (FormOperationBuilder $builder) {
                    $builder->visibleWhen(CarrierSettings::ALLOW_INTERNATIONAL_MAILBOX);
                });
            $this->makeReadOnlyWhenRequired($trackedElement, 'tracked');
            $fields[] = $trackedElement;
        }

        return $fields;
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
        $fields = [
            new SettingsDivider($this->createGenericLabel('export')),
        ];

        if ($this->carrierSchema->canHaveAgeCheck()) {
            $ageCheckElement = (new InteractiveElement(CarrierSettings::EXPORT_AGE_CHECK, Components::INPUT_TOGGLE))
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
                });
            $this->makeReadOnlyWhenRequired($ageCheckElement, 'requiresAgeVerification');
            $fields[] = [$ageCheckElement];
        }

        // Disable the signature / only recipient options when age check is enabled. With age check these are mandatory.
        $signatureElements = [];
        $onlyRecipientElements = [];

        if ($this->carrierSchema->canHaveSignature()) {
            $signatureElement = new InteractiveElement(CarrierSettings::EXPORT_SIGNATURE, Components::INPUT_TOGGLE);
            $this->makeReadOnlyWhenRequired($signatureElement, 'requiresSignature');
            $signatureElements = [$signatureElement];
        }

        if ($this->carrierSchema->canHaveOnlyRecipient()) {
            $onlyRecipientElement = new InteractiveElement(CarrierSettings::EXPORT_ONLY_RECIPIENT, Components::INPUT_TOGGLE);
            $this->makeReadOnlyWhenRequired($onlyRecipientElement, 'recipientOnlyDelivery');
            $onlyRecipientElements = [$onlyRecipientElement];
        }

        $fields[] = $this->withOperation(
            function (FormOperationBuilder $builder) {
                if (!$this->carrierSchema->canHaveAgeCheck()) {
                    return;
                }

                $builder->readOnlyWhen(CarrierSettings::EXPORT_AGE_CHECK);
            },
            $signatureElements,
            $onlyRecipientElements
        );

        if ($this->carrierSchema->canHaveReceiptCode()) {
            $receiptCodeElement = new InteractiveElement(CarrierSettings::EXPORT_RECEIPT_CODE, Components::INPUT_TOGGLE);
            $this->makeReadOnlyWhenRequired($receiptCodeElement, 'requiresReceiptCode');
            $fields[] = [$receiptCodeElement];
        }

        if ($this->carrierSchema->canHaveLargeFormat()) {
            $largeFormatElement = new InteractiveElement(CarrierSettings::EXPORT_LARGE_FORMAT, Components::INPUT_TOGGLE);
            $this->makeReadOnlyWhenRequired($largeFormatElement, 'oversizedPackage');
            $fields[] = [$largeFormatElement];
        }

        if ($this->carrierSchema->canHaveDirectReturn()) {
            $returnElement = new InteractiveElement(CarrierSettings::EXPORT_RETURN, Components::INPUT_TOGGLE);
            $this->makeReadOnlyWhenRequired($returnElement, 'returnOnFirstFailedDelivery');
            $fields[] = [$returnElement];
        }

        if ($this->carrierSchema->canHaveHideSender()) {
            $hideSenderElement = new InteractiveElement(CarrierSettings::EXPORT_HIDE_SENDER, Components::INPUT_TOGGLE);
            $this->makeReadOnlyWhenRequired($hideSenderElement, 'hideSender');
            $fields[] = [$hideSenderElement];
        }

        $fields[] = $this->carrierSchema->canHaveInsurance()
            ? $this->getExportInsuranceFields()
            : [];

        if ($this->carrierSchema->canHaveCollect()) {
            $collectElement = new InteractiveElement(CarrierSettings::EXPORT_COLLECT, Components::INPUT_TOGGLE);
            $this->makeReadOnlyWhenRequired($collectElement, 'scheduledCollection');
            $fields[] = [$collectElement];
        }

        if ($this->carrierSchema->canHaveFreshFood()) {
            $freshFoodElement = new InteractiveElement(CarrierSettings::EXPORT_FRESH_FOOD, Components::INPUT_TOGGLE);
            $this->makeReadOnlyWhenRequired($freshFoodElement, 'freshFood');
            $fields[] = [$freshFoodElement];
        }

        if ($this->carrierSchema->canHaveFrozen()) {
            $frozenElement = new InteractiveElement(CarrierSettings::EXPORT_FROZEN, Components::INPUT_TOGGLE);
            $this->makeReadOnlyWhenRequired($frozenElement, 'frozen');
            $fields[] = [$frozenElement];
        }

        return $fields;
    }

    /**
     * @return array
     */
    private function getDefaultExportReturnsFields(): array
    {
        $hasPackageTypeOptions = !empty($this->carrier->outboundFeatures->packageTypes);
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
                    'options' => $this->createPackageTypeOptions($this->carrier->packageTypes),
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
        $elements[] = (new InteractiveElement(CarrierSettings::DELIVERY_OPTIONS_ENABLED, Components::INPUT_TOGGLE))
            ->builder(function (FormOperationBuilder $builder) {
                $builder->afterUpdate(function (FormAfterUpdateBuilder $afterUpdate) {
                    foreach ($this->deliveryOptionsResetService->getDeliveryOptionSettings() as $setting) {
                        $afterUpdate
                            ->setValue(false)
                            ->on($setting)
                            ->if->eq(false);
                    }
                });
            });

        // Home delivery section
        $elements[] = $this->withOperation(
            function (FormOperationBuilder $builder) {
                $builder->visibleWhen(CarrierSettings::DELIVERY_OPTIONS_ENABLED);
            },
            new SettingsDivider($this->createGenericLabel('delivery_options_delivery'), SettingsDivider::LEVEL_3),
            new InteractiveElement(CarrierSettings::ALLOW_DELIVERY_OPTIONS, Components::INPUT_TOGGLE)
        );

        // At home delivery options configuration (when enabled)
        $homeDeliveryOptionsConfig = [
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
        $homeDeliveryOptionsConfig[] = new SettingsDivider($this->createGenericLabel('delivery_moments'), SettingsDivider::LEVEL_4);

        // Add same day delivery settings
        $homeDeliveryOptionsConfig = array_merge($homeDeliveryOptionsConfig, $this->getSameDayDeliverySettings());

        // Add saturday delivery settings
        $homeDeliveryOptionsConfig = array_merge($homeDeliveryOptionsConfig, $this->getSaturdayDeliverySettings());

        // Add monday delivery settings
        $homeDeliveryOptionsConfig = array_merge($homeDeliveryOptionsConfig, $this->getMondayDeliverySettings());

        // Add dynamic delivery type settings
        $homeDeliveryOptionsConfig = array_merge($homeDeliveryOptionsConfig, $this->getDeliveryTypeSettings());

        // Add shipment options section
        $homeDeliveryOptionsConfig[] = new SettingsDivider($this->createGenericLabel('shipment_options'), SettingsDivider::LEVEL_4);
        $homeDeliveryOptionsConfig = array_merge($homeDeliveryOptionsConfig, $this->getShipmentOptionsSettings());

        // Wrap delivery options config with visibility conditions
        $elements[] = $this->withOperation(
            function (FormOperationBuilder $builder) {
                $builder->visibleWhen(CarrierSettings::DELIVERY_OPTIONS_ENABLED)
                    ->and(CarrierSettings::ALLOW_DELIVERY_OPTIONS); // "allow home delivery" toggle
            },
            ...$homeDeliveryOptionsConfig
        );

        // Show pickup locations
        if (
            $this->carrier->deliveryTypes &&
            in_array(RefTypesDeliveryTypeV2::PICKUP, $this->carrier->deliveryTypes, true)
        ) {
            $pickupDeliveryOptionsConfig[] = new SettingsDivider($this->createGenericLabel('delivery_options_pickup'), SettingsDivider::LEVEL_3);
            $pickupDeliveryOptionsConfig = array_merge(
                $pickupDeliveryOptionsConfig,
                $this->createSettingWithPriceFields(
                    CarrierSettings::ALLOW_PICKUP_LOCATIONS,
                    CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP
                )
            );

            // Wrap pickup options config with visibility conditions
            $elements[] = $this->withOperation(
                function (FormOperationBuilder $builder) {
                    $builder->visibleWhen(CarrierSettings::DELIVERY_OPTIONS_ENABLED);
                },
                ...$pickupDeliveryOptionsConfig
            );
        }

        return $elements;
    }

    /**
     * Get same day delivery settings
     */
    private function getSameDayDeliverySettings(): array
    {
        if (!$this->carrierSchema->canHaveSameDayDelivery()) {
            return [];
        }

        return array_merge(
            $this->createSettingWithPriceFields(
                CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
                CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY_DELIVERY
            ),
            [new InteractiveElement(CarrierSettings::CUTOFF_TIME_SAME_DAY, Components::INPUT_TIME)]
        );
    }

    /**
     * Get saturday delivery setting based on carrier deliveryOptions config
     */
    private function getSaturdayDeliverySettings(): array
    {
        if (!$this->carrierSchema->canHaveSaturdayDelivery()) {
            return [];
        }

        return $this->createSettingWithPriceFields(
            CarrierSettings::ALLOW_SATURDAY_DELIVERY,
            CarrierSettings::PRICE_DELIVERY_TYPE_SATURDAY_DELIVERY
        );
    }

    /**
     * Get monday delivery setting based on carrier deliveryOptions config
     */
    private function getMondayDeliverySettings(): array
    {
        if (!$this->carrierSchema->canHaveMondayDelivery()) {
            return [];
        }

        return $this->createSettingWithPriceFields(
            CarrierSettings::ALLOW_MONDAY_DELIVERY,
            CarrierSettings::PRICE_DELIVERY_TYPE_MONDAY_DELIVERY
        );
    }

    /**
     * Get dynamic delivery type settings based on carrier delivery type config
     */
    private function getDeliveryTypeSettings(): array
    {
        $settings = [];

        if (!$this->carrier->deliveryTypes) {
            return $settings;
        }

        foreach ($this->carrier->deliveryTypes as $deliveryType) {
            // Ignore unsupported types and pickup (pickup is handled in a separate section in getDeliveryOptionsFields())
            if ($deliveryType === RefTypesDeliveryTypeV2::PICKUP) {
                continue;
            }

            // @TODO: in the future, make this fully dynamic by also allowing custom delivery types from carriers and not relying on predefined constants
            if (\defined(CarrierSettings::class . "::ALLOW_" . strtoupper($deliveryType))) {
                $typeAllowedSetting = constant(CarrierSettings::class . "::ALLOW_" . strtoupper($deliveryType));
            } else {
                continue;
            }

            if (\defined(CarrierSettings::class . "::PRICE_DELIVERY_TYPE_" . strtoupper($deliveryType))) {
                $typePriceSetting = constant(CarrierSettings::class . "::PRICE_DELIVERY_TYPE_" . strtoupper($deliveryType));
            } else {
                continue;
            }

            $settings = array_merge(
                $settings,
                $this->createSettingWithPriceFields(
                    $typeAllowedSetting,
                    $typePriceSetting
                )
            );
        }

        return $settings;
    }

    /**
     * Get shipment options settings based on carrier capabilities
     */
    private function getShipmentOptionsSettings(): array
    {
        $settings = [];

        // Signature option
        if ($this->carrierSchema->canHaveSignature()) {
            $elements = $this->createSettingWithPriceFields(
                CarrierSettings::ALLOW_SIGNATURE,
                CarrierSettings::PRICE_SIGNATURE
            );
            $this->makeReadOnlyWhenRequired($elements[0], 'requiresSignature');
            $settings = array_merge($settings, $elements);
        }

        // Only recipient option
        if ($this->carrierSchema->canHaveOnlyRecipient()) {
            $elements = $this->createSettingWithPriceFields(
                CarrierSettings::ALLOW_ONLY_RECIPIENT,
                CarrierSettings::PRICE_ONLY_RECIPIENT
            );
            $this->makeReadOnlyWhenRequired($elements[0], 'recipientOnlyDelivery');
            $settings = array_merge($settings, $elements);
        }

        // Priority delivery option
        if ($this->carrierSchema->canHavePriorityDelivery()) {
            $elements = $this->createSettingWithPriceFields(
                CarrierSettings::ALLOW_PRIORITY_DELIVERY,
                CarrierSettings::PRICE_PRIORITY_DELIVERY
            );
            $this->makeReadOnlyWhenRequired($elements[0], 'priorityDelivery');
            $settings = array_merge($settings, $elements);
        }

        return $settings;
    }

    /**
     * @return array
     */
    private function getExportInsuranceFields(): array
    {
        $insuranceAmounts = $this->carrierSchema->getAllowedInsuranceAmounts();

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
                            'min'  => Pdk::get('insurancePercentageMin'),
                            'step' => Pdk::get('insurancePercentageStep'),
                            'max'  => Pdk::get('insurancePercentageMax'),
                        ],
                    ]
                )
            ),
        ];
    }

    /**
     * Mark a form element as read-only when the carrier's capability metadata indicates it is required.
     *
     * @param  \MyParcelNL\Pdk\Frontend\Form\InteractiveElement $element
     * @param  string                                           $capabilitiesKey
     *
     * @return void
     */
    private function makeReadOnlyWhenRequired(InteractiveElement $element, string $capabilitiesKey): void
    {
        $option = $this->carrier->getOptionMetadata($capabilitiesKey);

        if (! $option || ! $option->getIsRequired()) {
            return;
        }

        $element->builder(function (FormOperationBuilder $builder) {
            $builder->readOnlyWhen();
        });
    }

    /**
     * @return string
     */
    private function getFormattedCarrierName(): string
    {
        return Str::snake(str_replace('.', '_', Str::lower($this->carrier->carrier)));
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Form\InteractiveElement[]
     */
    private function getPackageTypeFields(): array
    {
        $allowedPackageTypes = $this->carrier->packageTypes ?? [];

        $fields = [
            new InteractiveElement(CarrierSettings::DEFAULT_PACKAGE_TYPE, Components::INPUT_SELECT, [
                'options' => $this->createPackageTypeOptions($allowedPackageTypes),
            ]),
        ];

        if (in_array(RefShipmentPackageTypeV2::SMALL_PACKAGE, $allowedPackageTypes, true)) {
            $fields[] = new InteractiveElement(
                CarrierSettings::PRICE_PACKAGE_TYPE_PACKAGE_SMALL,
                Components::INPUT_CURRENCY
            );
        }

        if (in_array(RefShipmentPackageTypeV2::MAILBOX, $allowedPackageTypes, true)) {
            $fields[] = new InteractiveElement(
                CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
                Components::INPUT_CURRENCY
            );

            $fields[] = $this->createInternationalMailboxFields();
        }

        if (in_array(RefShipmentPackageTypeV2::DIGITAL_STAMP, $allowedPackageTypes, true)) {
            $fields[] = new InteractiveElement(
                CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
                Components::INPUT_CURRENCY
            );
        }

        return $fields;
    }
}
