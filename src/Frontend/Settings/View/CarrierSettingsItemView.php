<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\PlainElement;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Validation\Repository\SchemaRepository;
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

    /**
     * @return void
     */
    private function gatherElements(): array
    {
        $repo        = Pdk::get(SchemaRepository::class);
        $carrierName = $this->getFormattedCarrierName();
        $schema      = $repo->getOrderValidationSchema($carrierName, CountryService::CC_NL);
        $key         = 'properties.deliveryOptions.properties.packageType';
        $elements    = [
            new InteractiveElement(CarrierSettings::ALLOW_DELIVERY_OPTIONS, Components::INPUT_TOGGLE),
            new InteractiveElement(CarrierSettings::ALLOW_MONDAY_DELIVERY, Components::INPUT_TOGGLE),
            new InteractiveElement(CarrierSettings::ALLOW_SATURDAY_DELIVERY, Components::INPUT_TOGGLE),
            new InteractiveElement(CarrierSettings::ALLOW_PICKUP_LOCATIONS, Components::INPUT_TOGGLE),

            new InteractiveElement(CarrierSettings::DEFAULT_PACKAGE_TYPE, Components::INPUT_SELECT, [
                'options' => $this->toSelectOptions($repo->validOptions($schema, $key)),
            ]),
            new InteractiveElement(CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX, Components::INPUT_CURRENCY),
            new InteractiveElement(CarrierSettings::DIGITAL_STAMP_DEFAULT_WEIGHT, Components::INPUT_SELECT),
            new InteractiveElement(CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP, Components::INPUT_CURRENCY),
        ];

        if ($this->carrierSchema->canHaveSignature()) {
            $elements[] = new InteractiveElement(CarrierSettings::ALLOW_SIGNATURE, Components::INPUT_TOGGLE);
            $elements[] = new InteractiveElement(CarrierSettings::PRICE_SIGNATURE, Components::INPUT_CURRENCY);
        }

        if ($this->carrierSchema->canHaveOnlyRecipient()) {
            $elements[] = new InteractiveElement(CarrierSettings::ALLOW_ONLY_RECIPIENT, Components::INPUT_TOGGLE);
            $elements[] = new InteractiveElement(CarrierSettings::PRICE_ONLY_RECIPIENT, Components::INPUT_CURRENCY);
        }

        if ($this->carrierSchema->canHaveMorningDelivery()) {
            $elements[] = new InteractiveElement(CarrierSettings::ALLOW_MORNING_DELIVERY, Components::INPUT_TOGGLE);
            $elements[] = new InteractiveElement(CarrierSettings::PRICE_DELIVERY_TYPE_MORNING, Components::INPUT_CURRENCY);
        }

        if ($this->carrierSchema->canHaveEveningDelivery()) {
            $elements[] = new InteractiveElement(CarrierSettings::ALLOW_EVENING_DELIVERY, Components::INPUT_TOGGLE);
            $elements[] = new InteractiveElement(CarrierSettings::PRICE_DELIVERY_TYPE_EVENING, Components::INPUT_CURRENCY);
        }

        if ($this->carrierSchema->canHaveDate()) {
            // todo make custom element for drop-off
            $elements[] = new InteractiveElement(CarrierSettings::DROP_OFF_POSSIBILITIES, Components::INPUT_SELECT);
            $elements[] = new InteractiveElement(CarrierSettings::SHOW_DELIVERY_DAY, Components::INPUT_TOGGLE);
        }

        if ($this->carrierSchema->canHaveInsurance()) {
            $elements[] = new InteractiveElement(CarrierSettings::EXPORT_INSURANCE, Components::INPUT_TOGGLE);
            $elements[] = new InteractiveElement(
                CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT, Components::INPUT_NUMBER
            );
            if (($element = $this->getInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO, CountryService::CC_NL))) {
                $elements[] = $element;
            }
            if (($element = $this->getInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO_BE, CountryService::CC_BE))) {
                $elements[] = $element;
            }
            if (($element = $this->getInsuranceElement(CarrierSettings::EXPORT_INSURANCE_UP_TO_EU, CountryService::CC_FR))) {
                $elements[] = $element;
            }
        }

        if ($this->carrierSchema->canHaveSameDayDelivery()) {
            $elements[] = new InteractiveElement(CarrierSettings::ALLOW_SAME_DAY_DELIVERY, Components::INPUT_TOGGLE);
        }

        $elements[] = new PlainElement('Heading', ['$slot' => 'settings_carrier_export']);

        if ($this->carrierSchema->canHaveAgeCheck()) {
            $elements[] = new InteractiveElement(CarrierSettings::EXPORT_AGE_CHECK, Components::INPUT_TOGGLE);
        }

        if ($this->carrierSchema->canHaveOnlyRecipient()) {
            $elements[] = new InteractiveElement(CarrierSettings::EXPORT_ONLY_RECIPIENT, Components::INPUT_TOGGLE);
        }

        if ($this->carrierSchema->canHaveSignature()) {
            $elements[] = new InteractiveElement(CarrierSettings::EXPORT_SIGNATURE, Components::INPUT_TOGGLE);
        }

        if ($this->carrierSchema->canHaveInsurance()) {
            $elements[] = new InteractiveElement(CarrierSettings::EXPORT_INSURANCE, Components::INPUT_TOGGLE);
            $elements[] = new InteractiveElement(CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT, Components::INPUT_TEXT);
            $elements[] = new InteractiveElement(CarrierSettings::EXPORT_INSURANCE_UP_TO, Components::INPUT_SELECT);
        }

        if ($this->carrierSchema->canHaveLargeFormat()) {
            $elements[] = new InteractiveElement(CarrierSettings::EXPORT_LARGE_FORMAT, Components::INPUT_TOGGLE);
        }

        $elements[] = new InteractiveElement(CarrierSettings::EXPORT_RETURN_SHIPMENTS, Components::INPUT_TOGGLE);

        return $elements;
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
        $repo   = Pdk::get(SchemaRepository::class);
        $schema = $repo->getOrderValidationSchema($this->getFormattedCarrierName(), $cc, 'package');
        $key    = 'properties.deliveryOptions.properties.shipmentOptions.properties.insurance';

        if (($options = $repo->validOptions($schema, $key))) {

            $options = array_map(static function ($option) {
                return $option / 100;
            }, $options);

            return new InteractiveElement(
                $name,
                Components::INPUT_SELECT,
                [
                    'options' => $this->toSelectOptions($options),
                ]
            );
        }

        return null;
    }
}
