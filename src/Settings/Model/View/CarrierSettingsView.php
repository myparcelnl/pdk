<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model\View;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Form\Model\Input\Select\DropOffDaySelect;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Form\Model\Input\ToggleInput;

/**
 * @property \MyParcelNL\Pdk\Form\Model\Input\ToggleInput  $allowDeliveryOptions
 * @property \MyParcelNL\Pdk\Form\Model\Input\ToggleInput  $allowEveningDelivery
 * @property \MyParcelNL\Pdk\Form\Model\Input\ToggleInput  $allowMondayDelivery
 * @property \MyParcelNL\Pdk\Form\Model\Input\ToggleInput  $allowMorningDelivery
 * @property \MyParcelNL\Pdk\Form\Model\Input\ToggleInput  $allowOnlyRecipient
 * @property \MyParcelNL\Pdk\Form\Model\Input\ToggleInput  $allowPickupLocations
 * @property \MyParcelNL\Pdk\Form\Model\Input\ToggleInput  $allowSameDayDelivery
 * @property \MyParcelNL\Pdk\Form\Model\Input\ToggleInput  $allowSaturdayDelivery
 * @property \MyParcelNL\Pdk\Form\Model\Input\ToggleInput  $allowSignature
 * @property                                               $cutoffTime
 * @property                                               $cutoffTimeSameDay
 * @property                                               $dropOffDays
 * @property \MyParcelNL\Pdk\Form\Model\Input\ToggleInput  $featureShowDeliveryDate
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput    $priceEveningDelivery
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput    $priceMorningDelivery
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput    $priceOnlyRecipient
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput    $pricePackageTypeDigitalStamp
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput    $pricePackageTypeMailbox
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput    $pricePickup
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput    $priceSameDayDelivery
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput    $priceSignature
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput    $priceStandardDelivery
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
        'dropOffDays'                  => DropOffDaySelect::class,
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
        'dropOffDays'                  => DropOffDaySelect::class,
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
