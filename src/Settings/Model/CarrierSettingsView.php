<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Form\Inputs\Model\CheckboxInput;
use MyParcelNL\Pdk\Form\Inputs\Model\DropOffDaySelector;
use MyParcelNL\Pdk\Form\Inputs\Model\HiddenInput;
use MyParcelNL\Pdk\Form\Inputs\Model\SelectInput;
use MyParcelNL\Pdk\Form\Inputs\Model\ToggleInput;
use MyParcelNL\Pdk\Form\Inputs\Model\TextInput;

/**
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\ToggleInput $allowDeliveryOptions
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\ToggleInput $allowEveningDelivery
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\ToggleInput $allowMondayDelivery
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\ToggleInput $allowMorningDelivery
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\ToggleInput $allowOnlyRecipient
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\ToggleInput $allowPickupLocations
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\ToggleInput $allowSameDayDelivery
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\ToggleInput $allowSaturdayDelivery
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\ToggleInput $allowSignature
 * @property                                               $cutoffTime
 * @property                                               $cutoffTimeSameDay
 * @property                                               $dropOffDays
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\ToggleInput $featureShowDeliveryDate
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\TextInput   $priceEveningDelivery
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\TextInput   $priceMorningDelivery
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\TextInput   $priceOnlyRecipient
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\TextInput   $pricePackageTypeDigitalStamp
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\TextInput   $pricePackageTypeMailbox
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\TextInput   $pricePickup
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\TextInput   $priceSameDayDelivery
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\TextInput   $priceSignature
 * @property \MyParcelNL\Pdk\Form\Inputs\Model\TextInput   $priceStandardDelivery
 */
class CarrierSettingsView extends Model
{
    public const INPUT_SELECT      = 'select';
    public const INPUT_TEXT        = 'text';
    public const INPUT_TOGGLE      = 'switch';
    public const INPUT_CHECKBOX    = 'checkbox';
    public const INPUT_HIDDEN      = 'hidden';
    public const INPUT_DATE_SELECT = 'date_selector';

    protected $attributes = [
        'allowDeliveryOptions'         => ToggleInput::class,
        'allowEveningDelivery'         => ToggleInput::class,
        'allowMondayDelivery'          => ToggleInput::class,
        'allowMorningDelivery'         => ToggleInput::class,
        'allowOnlyRecipient'           => ToggleInput::class,
        'allowPickupLocations'         => ToggleInput::class,
        'allowSameDayDelivery'         => ToggleInput::class,
        'allowSaturdayDelivery'        => ToggleInput::class,
        'allowSignature'               => ToggleInput::class,
        'cutoffTime'                   => null,
        'cutoffTimeSameDay'            => null,
        'dropOffDays'                  => DropOffDaySelector::class,
        'featureShowDeliveryDate'      => SelectInput::class,
        'priceEveningDelivery'         => TextInput::class,
        'priceMorningDelivery'         => TextInput::class,
        'priceOnlyRecipient'           => TextInput::class,
        'pricePackageTypeDigitalStamp' => TextInput::class,
        'pricePackageTypeMailbox'      => TextInput::class,
        'pricePickup'                  => TextInput::class,
        'priceSameDayDelivery'         => TextInput::class,
        'priceSignature'               => TextInput::class,
        'priceStandardDelivery'        => TextInput::class,
    ];

    protected $casts      = [
        'allowDeliveryOptions'         => ToggleInput::class,
        'allowEveningDelivery'         => ToggleInput::class,
        'allowMondayDelivery'          => ToggleInput::class,
        'allowMorningDelivery'         => ToggleInput::class,
        'allowOnlyRecipient'           => ToggleInput::class,
        'allowPickupLocations'         => ToggleInput::class,
        'allowSameDayDelivery'         => ToggleInput::class,
        'allowSaturdayDelivery'        => ToggleInput::class,
        'allowSignature'               => ToggleInput::class,
        'cutoffTime'                   => 'string',
        'cutoffTimeSameDay'            => 'string',
        'dropOffDays'                  => DropOffDaySelector::class,
        'featureShowDeliveryDate'      => SelectInput::class,
        'priceEveningDelivery'         => TextInput::class,
        'priceMorningDelivery'         => TextInput::class,
        'priceOnlyRecipient'           => TextInput::class,
        'pricePackageTypeDigitalStamp' => TextInput::class,
        'pricePackageTypeMailbox'      => TextInput::class,
        'pricePickup'                  => TextInput::class,
        'priceSameDayDelivery'         => TextInput::class,
        'priceSignature'               => TextInput::class,
        'priceStandardDelivery'        => TextInput::class,
    ];

    /**
     * @param  array $data
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Model\InvalidCastException
     */
    public static function createInput(array $data)
    {
        $inputClass = (new CarrierSettingsView)->getAttributeValue($data['name']);

        return new $inputClass($data);
    }
}
