<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SameDayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SaturdayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\App\Options\Definition\TrackedDefinition;
use MyParcelNL\Pdk\App\Service\DeliveryOptionsResetService;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Support\SettingKey;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Service\CarrierValidationService;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormAfterUpdateBuilder;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\SettingsDivider;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrierV2;
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
     * @var \MyParcelNL\Pdk\Carrier\Service\CarrierValidationService
     */
    protected $carrierValidationService;

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
        $this->carrierValidationService    = Pdk::get(CarrierValidationService::class);
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
        $hasInsurance = $this->carrierValidationService->supportsShipmentOption($this->carrier, InsuranceDefinition::class);

        $insuranceAmounts = $hasInsurance
            ? $this->carrierValidationService->getAllowedInsuranceAmounts($this->carrier)
            : [];

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
        $allowInternationalMailboxKey = SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_INTERNATIONAL_MAILBOX);

        $fields = $this->createSettingWithPriceFields(
            $allowInternationalMailboxKey,
            SettingKey::price(DeliveryOptions::DELIVERY_OPTION_INTERNATIONAL_MAILBOX)
        );

        // Add tracked toggle for carriers with custom mailbox contract, only visible when international mailbox is enabled
        if ($this->carrierValidationService->supportsShipmentOption($this->carrier, TrackedDefinition::class) && AccountSettings::hasCarrierSmallPackageContract()) {
            $trackedElement = (new InteractiveElement((new TrackedDefinition())->getCarrierSettingsKey(), Components::INPUT_TOGGLE))
                ->builder(function (FormOperationBuilder $builder) use ($allowInternationalMailboxKey) {
                    $builder->visibleWhen($allowInternationalMailboxKey);
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
        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        // Definitions with custom UI handling — processed explicitly below, then excluded from the generic loop
        $specialCasedDefinitions = [
            AgeCheckDefinition::class,
            SignatureDefinition::class,
            OnlyRecipientDefinition::class,
            InsuranceDefinition::class,
        ];

        $ageCheckDefinition      = null;
        $signatureDefinition     = null;
        $onlyRecipientDefinition = null;

        foreach ($definitions as $definition) {
            if ($definition instanceof AgeCheckDefinition) {
                $ageCheckDefinition = $definition;
            } elseif ($definition instanceof SignatureDefinition) {
                $signatureDefinition = $definition;
            } elseif ($definition instanceof OnlyRecipientDefinition) {
                $onlyRecipientDefinition = $definition;
            }
        }

        $fields = [
            new SettingsDivider($this->createGenericLabel('export')),
        ];

        // Age check toggle with afterUpdate logic (forces signature + only recipient on)
        if ($ageCheckDefinition && $this->carrierValidationService->supportsShipmentOption($this->carrier, $ageCheckDefinition)) {
            $signatureKey     = $signatureDefinition ? $signatureDefinition->getCarrierSettingsKey() : null;
            $onlyRecipientKey = $onlyRecipientDefinition ? $onlyRecipientDefinition->getCarrierSettingsKey() : null;

            $ageCheckElement = (new InteractiveElement($ageCheckDefinition->getCarrierSettingsKey(), Components::INPUT_TOGGLE))
                ->builder(function (FormOperationBuilder $builder) use ($signatureKey, $onlyRecipientKey) {
                    $builder->afterUpdate(function (FormAfterUpdateBuilder $afterUpdate) use ($signatureKey, $onlyRecipientKey) {
                        // The admin form normalizes toggle values to TriState ints (1/0/-1)
                        // at the component boundary, so the strict `$eq` checks below compare
                        // int against int. Turning age check OFF deliberately leaves signature
                        // and only recipient stored as enabled — they just unlock again, so
                        // what the merchant sees is exactly what is stored.
                        if ($signatureKey) {
                            $afterUpdate->setValue(TriStateService::ENABLED)->on($signatureKey)->if->eq(TriStateService::ENABLED);
                        }
                        if ($onlyRecipientKey) {
                            $afterUpdate->setValue(TriStateService::ENABLED)->on($onlyRecipientKey)->if->eq(TriStateService::ENABLED);
                        }
                    });
                });
            $this->makeReadOnlyWhenRequired($ageCheckElement, $ageCheckDefinition->getCapabilitiesOptionsKey());
            $fields[] = [$ageCheckElement];
        }

        // Signature and only recipient — read-only when age check is enabled
        $signatureElements     = [];
        $onlyRecipientElements = [];

        if ($signatureDefinition && $this->carrierValidationService->supportsShipmentOption($this->carrier, $signatureDefinition)) {
            $signatureElement = new InteractiveElement($signatureDefinition->getCarrierSettingsKey(), Components::INPUT_TOGGLE);
            $this->makeReadOnlyWhenRequired($signatureElement, $signatureDefinition->getCapabilitiesOptionsKey());
            $signatureElements = [$signatureElement];
        }

        if ($onlyRecipientDefinition && $this->carrierValidationService->supportsShipmentOption($this->carrier, $onlyRecipientDefinition)) {
            $onlyRecipientElement = new InteractiveElement($onlyRecipientDefinition->getCarrierSettingsKey(), Components::INPUT_TOGGLE);
            $this->makeReadOnlyWhenRequired($onlyRecipientElement, $onlyRecipientDefinition->getCapabilitiesOptionsKey());
            $onlyRecipientElements = [$onlyRecipientElement];
        }

        $fields[] = $this->withOperation(
            function (FormOperationBuilder $builder) use ($ageCheckDefinition) {
                if (! $ageCheckDefinition || ! $this->carrierValidationService->supportsShipmentOption($this->carrier, $ageCheckDefinition)) {
                    return;
                }
                // Lock only while age check is explicitly enabled. A bare (truthy) check
                // would also match INHERIT (-1) and lock the fields while age check was
                // never turned on.
                $builder->readOnlyWhen($ageCheckDefinition->getCarrierSettingsKey(), TriStateService::ENABLED);
            },
            $signatureElements,
            $onlyRecipientElements
        );

        // Generic loop for all other export options
        foreach ($definitions as $definition) {
            $carrierKey = $definition->getCarrierSettingsKey();

            if (! $carrierKey || ! $this->carrierValidationService->supportsShipmentOption($this->carrier, $definition)) {
                continue;
            }

            if (in_array(get_class($definition), $specialCasedDefinitions, true)) {
                continue;
            }

            $element = new InteractiveElement($carrierKey, Components::INPUT_TOGGLE);

            $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

            if ($capabilitiesKey) {
                $this->makeReadOnlyWhenRequired($element, $capabilitiesKey);
            }

            $fields[] = [$element];
        }

        // Insurance — custom SELECT UI handled after the generic loop
        if ($this->carrierValidationService->supportsShipmentOption($this->carrier, InsuranceDefinition::class)) {
            $fields[] = $this->getExportInsuranceFields();
        }

        return $fields;
    }

    /**
     * @return array
     */
    private function getDefaultExportReturnsFields(): array
    {
        $hasPackageTypeOptions = !empty($this->carrier->packageTypes);
        $canHaveLargeFormat    = $this->carrierValidationService->supportsShipmentOption($this->carrier, LargeFormatDefinition::class);

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
                    // Use the disabled scalar value here so it matches the toggle input's
                    // value representation and the frontend's strict `$eq` check succeeds.
                    foreach ($this->deliveryOptionsResetService->getDeliveryOptionSettings() as $setting) {
                        $afterUpdate
                            ->setValue(TriStateService::DISABLED)
                            ->on($setting)
                            ->if->eq(TriStateService::DISABLED);
                    }
                });
            });

        // Home delivery section
        $elements[] = $this->withOperation(
            function (FormOperationBuilder $builder) {
                $builder->visibleWhen(CarrierSettings::DELIVERY_OPTIONS_ENABLED);
            },
            new SettingsDivider($this->createGenericLabel('delivery_options_delivery'), SettingsDivider::LEVEL_3),
            new InteractiveElement(SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_ALLOW_HOME), Components::INPUT_TOGGLE)
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
                    ->and(SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_ALLOW_HOME)); // "allow home delivery" toggle
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
                    SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP),
                    SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::PICKUP)
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
        if (!$this->carrierValidationService->supportsShipmentOption($this->carrier, SameDayDeliveryDefinition::class)) {
            return [];
        }

        return array_merge(
            $this->createSettingWithPriceFields(
                (new SameDayDeliveryDefinition())->getAllowSettingsKey(),
                SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::SAME_DAY)
            ),
            [new InteractiveElement(CarrierSettings::CUTOFF_TIME_SAME_DAY, Components::INPUT_TIME)]
        );
    }

    /**
     * Get saturday delivery setting based on carrier deliveryOptions config
     */
    private function getSaturdayDeliverySettings(): array
    {
        if (!$this->carrierValidationService->supportsShipmentOption($this->carrier, SaturdayDeliveryDefinition::class)) {
            return [];
        }

        return $this->createSettingWithPriceFields(
            (new SaturdayDeliveryDefinition())->getAllowSettingsKey(),
            SettingKey::priceDeliveryType(DeliveryOptions::DELIVERY_OPTION_SATURDAY)
        );
    }

    /**
     * Get monday delivery setting based on carrier deliveryOptions config
     */
    private function getMondayDeliverySettings(): array
    {
        if (!$this->carrierOffersMondayDelivery()) {
            return [];
        }

        return $this->createSettingWithPriceFields(
            SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_MONDAY),
            SettingKey::priceDeliveryType(DeliveryOptions::DELIVERY_OPTION_MONDAY)
        );
    }

    /**
     * Whether the carrier offers Monday delivery. Currently a hardcoded fact:
     * only PostNL exposes Monday delivery in the delivery-options widget.
     * NOT a capabilities concern (no API field), so it stays local to the view.
     */
    private function carrierOffersMondayDelivery(): bool
    {
        return $this->carrier->carrier === RefTypesCarrierV2::POSTNL;
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
            // Pickup has its own section in getDeliveryOptionsFields(), so skip it here.
            if ($deliveryType === RefTypesDeliveryTypeV2::PICKUP) {
                continue;
            }

            $settings = array_merge(
                $settings,
                $this->createSettingWithPriceFields(
                    SettingKey::allow($deliveryType),
                    SettingKey::priceDeliveryType($deliveryType)
                )
            );
        }

        return $settings;
    }

    /**
     * Get shipment options settings based on carrier capabilities.
     * Uses registered option definitions to dynamically build delivery options toggles.
     */
    private function getShipmentOptionsSettings(): array
    {
        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');
        $settings    = [];

        foreach ($definitions as $definition) {
            $allowKey = $definition->getAllowSettingsKey();
            $priceKey = $definition->getPriceSettingsKey();

            if (! $allowKey || ! $this->carrierValidationService->supportsShipmentOption($this->carrier, $definition)) {
                continue;
            }

            if ($priceKey) {
                $elements = $this->createSettingWithPriceFields($allowKey, $priceKey);
            } else {
                $elements = [new InteractiveElement($allowKey, Components::INPUT_TOGGLE)];
            }

            $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

            if ($capabilitiesKey) {
                $this->makeReadOnlyWhenRequired($elements[0], $capabilitiesKey);
            }

            $settings = array_merge($settings, $elements);
        }

        return $settings;
    }

    /**
     * @return array
     */
    private function getExportInsuranceFields(): array
    {
        $insuranceAmounts = $this->carrierValidationService->getAllowedInsuranceAmounts($this->carrier);

        if (count($insuranceAmounts) <= 1) {
            return [];
        }

        $exportInsuranceKey = (new InsuranceDefinition())->getCarrierSettingsKey();

        return [
            new InteractiveElement($exportInsuranceKey, Components::INPUT_TOGGLE),

            $this->withOperation(
                function (FormOperationBuilder $builder) use ($exportInsuranceKey) {
                    $builder->visibleWhen($exportInsuranceKey);
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

        foreach ($allowedPackageTypes as $packageType) {
            // Default package type's price is the carrier's basePrice, not a surcharge.
            if ($packageType === DeliveryOptions::DEFAULT_PACKAGE_TYPE_V2) {
                continue;
            }

            // Skip V2 values the PDK has no mapping for; their settings attrs do not exist.
            if (! DeliveryOptions::isPackageTypeSupported($packageType)) {
                continue;
            }

            $fields[] = new InteractiveElement(
                SettingKey::pricePackageType($packageType),
                Components::INPUT_CURRENCY
            );

            // Mailbox carries an extra international-mailbox configuration sibling.
            if ($packageType === RefShipmentPackageTypeV2::MAILBOX) {
                $fields[] = $this->createInternationalMailboxFields();
            }
        }

        return $fields;
    }
}
