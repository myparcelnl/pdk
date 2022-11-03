<?php
/** @noinspection PhpUnhandledExceptionInspection, PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Settings\View\AbstractView;
use MyParcelNL\Pdk\Settings\View\CarrierSettingsView;
use MyParcelNL\Pdk\Settings\View\CheckoutSettingsView;
use MyParcelNL\Pdk\Settings\View\CustomsSettingsView;
use MyParcelNL\Pdk\Settings\View\GeneralSettingsView;
use MyParcelNL\Pdk\Settings\View\LabelSettingsView;
use MyParcelNL\Pdk\Settings\View\OrderSettingsView;
use MyParcelNL\Pdk\Settings\View\ProductSettingsView;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::create());
});

/**
 * Shortcut to not have to write ~260 lines for the country options.
 *
 * @param  int $key
 *
 * @return array
 */
function addCountryOptions(int $key): array
{
    $keys = [];
    $i    = 0;

    foreach (CountryService::ALL as $countryCode) {
        $keys["$key.options.$i"] = $countryCode;
        $i++;
    }

    return $keys;
}

it('gets settings view', function (string $class, $output) {
    $viewFields = (new $class())->toArray();

    expect(Arr::dot($viewFields))->toEqual($output);
})->with([
    'general settings'  => [
        'class'  => GeneralSettingsView::class,
        'output' => [
            '0.name'  => GeneralSettings::API_KEY,
            '0.label' => 'settings_general_api_key',
            '0.type'  => 'TextInput',
            '1.name'  => GeneralSettings::API_LOGGING,
            '1.label' => 'settings_general_api_logging',
            '1.type'  => 'ToggleInput',
            '2.name'  => GeneralSettings::SHARE_CUSTOMER_INFORMATION,
            '2.label' => 'settings_general_share_customer_information',
            '2.type'  => 'ToggleInput',
            '3.name'  => GeneralSettings::CONCEPT_SHIPMENTS,
            '3.label' => 'settings_general_concept_shipments',
            '3.type'  => 'ToggleInput',
            '4.name'  => GeneralSettings::ORDER_MODE,
            '4.label' => 'settings_general_order_mode',
            '4.type'  => 'ToggleInput',
            '5.name'  => GeneralSettings::TRACK_TRACE_IN_EMAIL,
            '5.label' => 'settings_general_track_trace_in_email',
            '5.type'  => 'ToggleInput',
            '6.name'  => GeneralSettings::TRACK_TRACE_IN_ACCOUNT,
            '6.label' => 'settings_general_track_trace_in_account',
            '6.type'  => 'ToggleInput',
            '7.name'  => GeneralSettings::BARCODE_IN_NOTE,
            '7.label' => 'settings_general_barcode_in_note',
            '7.type'  => 'ToggleInput',
            '8.name'  => GeneralSettings::PROCESS_DIRECTLY,
            '8.label' => 'settings_general_process_directly',
            '8.type'  => 'ToggleInput',
        ],
    ],
    'carrier settings'  => [
        'class'  => CarrierSettingsView::class,
        'output' => [
            '0.name'        => CarrierSettings::ALLOW_DELIVERY_OPTIONS,
            '0.label'       => 'settings_carrier_allow_delivery_options',
            '0.type'        => 'ToggleInput',
            '1.name'        => CarrierSettings::ALLOW_EVENING_DELIVERY,
            '1.label'       => 'settings_carrier_allow_evening_delivery',
            '1.type'        => 'ToggleInput',
            '2.name'        => CarrierSettings::ALLOW_MONDAY_DELIVERY,
            '2.label'       => 'settings_carrier_allow_monday_delivery',
            '2.description' => 'settings_carrier_allow_monday_delivery_description',
            '2.type'        => 'ToggleInput',
            '3.name'        => CarrierSettings::ALLOW_MORNING_DELIVERY,
            '3.label'       => 'settings_carrier_allow_morning_delivery',
            '3.type'        => 'ToggleInput',
            '4.name'        => CarrierSettings::ALLOW_ONLY_RECIPIENT,
            '4.label'       => 'settings_carrier_allow_only_recipient',
            '4.type'        => 'ToggleInput',
            '5.name'        => CarrierSettings::ALLOW_PICKUP_LOCATIONS,
            '5.label'       => 'settings_carrier_allow_pickup_locations',
            '5.type'        => 'ToggleInput',
            '6.name'        => CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
            '6.label'       => 'settings_carrier_allow_same_day_delivery',
            '6.type'        => 'ToggleInput',
            '7.name'        => CarrierSettings::ALLOW_SATURDAY_DELIVERY,
            '7.label'       => 'settings_carrier_allow_saturday_delivery',
            '7.type'        => 'ToggleInput',
            '8.name'        => CarrierSettings::ALLOW_SIGNATURE,
            '8.label'       => 'settings_carrier_allow_signature',
            '8.type'        => 'ToggleInput',
            '9.name'        => CarrierSettings::DROP_OFF_POSSIBILITIES,
            '9.label'       => 'settings_carrier_drop_off_days',
            '9.type'        => 'DropOffDaySelectInput',
            '10.name'       => CarrierSettings::FEATURE_SHOW_DELIVERY_DATE,
            '10.label'      => 'settings_carrier_feature_show_delivery_date',
            '10.type'       => 'ToggleInput',
            '11.name'       => CarrierSettings::PRICE_EVENING_DELIVERY,
            '11.label'      => 'settings_carrier_price_evening_delivery',
            '11.type'       => 'TextInput',
            '12.name'       => CarrierSettings::PRICE_MORNING_DELIVERY,
            '12.label'      => 'settings_carrier_price_morning_delivery',
            '12.type'       => 'TextInput',
            '13.name'       => CarrierSettings::PRICE_ONLY_RECIPIENT,
            '13.label'      => 'settings_carrier_price_only_recipient',
            '13.type'       => 'TextInput',
            '14.name'       => CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
            '14.label'      => 'settings_carrier_price_package_type_digital_stamp',
            '14.type'       => 'TextInput',
            '15.name'       => CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
            '15.label'      => 'settings_carrier_price_package_type_mailbox',
            '15.type'       => 'TextInput',
            '16.name'       => CarrierSettings::PRICE_PICKUP,
            '16.label'      => 'settings_carrier_price_pickup',
            '16.type'       => 'TextInput',
            '17.name'       => CarrierSettings::PRICE_SAME_DAY_DELIVERY,
            '17.label'      => 'settings_carrier_price_same_day_delivery',
            '17.type'       => 'TextInput',
            '18.name'       => CarrierSettings::PRICE_SIGNATURE,
            '18.label'      => 'settings_carrier_price_signature',
            '18.type'       => 'TextInput',
            '19.name'       => CarrierSettings::PRICE_STANDARD_DELIVERY,
            '19.label'      => 'settings_carrier_price_standard_delivery',
            '19.type'       => 'TextInput',
            '20.name'       => CarrierSettings::DEFAULT_PACKAGE_TYPE,
            '20.label'      => 'settings_carrier_default_package_type',
            '20.type'       => 'SelectInput',
            '21.name'       => CarrierSettings::EXPORT_AGE_CHECK,
            '21.label'      => 'settings_carrier_export_age_check',
            '21.type'       => 'ToggleInput',
            '22.name'       => CarrierSettings::EXPORT_INSURANCE,
            '22.label'      => 'settings_carrier_export_insurance',
            '22.type'       => 'ToggleInput',
            '23.name'       => CarrierSettings::EXPORT_INSURANCE_AMOUNT,
            '23.label'      => 'settings_carrier_export_insurance_amount',
            '23.type'       => 'TextInput',
            '24.name'       => CarrierSettings::EXPORT_INSURANCE_UP_TO,
            '24.label'      => 'settings_carrier_export_insurance_up_to',
            '24.type'       => 'SelectInput',
            '25.name'       => CarrierSettings::ALLOW_INSURANCE_BELGIUM,
            '25.label'      => 'settings_carrier_allow_insurance_belgium',
            '25.type'       => 'ToggleInput',
            '26.name'       => CarrierSettings::EXPORT_LARGE_FORMAT,
            '26.label'      => 'settings_carrier_export_large_format',
            '26.type'       => 'ToggleInput',
            '27.name'       => CarrierSettings::EXPORT_ONLY_RECIPIENT,
            '27.label'      => 'settings_carrier_export_only_recipient',
            '27.type'       => 'ToggleInput',
            '28.name'       => CarrierSettings::EXPORT_RETURN_SHIPMENTS,
            '28.label'      => 'settings_carrier_export_return_shipments',
            '28.type'       => 'ToggleInput',
            '29.name'       => CarrierSettings::EXPORT_SIGNATURE,
            '29.label'      => 'settings_carrier_export_signature',
            '29.type'       => 'ToggleInput',
            '30.name'       => CarrierSettings::DIGITAL_STAMP_DEFAULT_WEIGHT,
            '30.label'      => 'settings_carrier_digital_stamp_default_weight',
            '30.type'       => 'SelectInput',
        ],
    ],
    'order settings'    => [
        'class'  => OrderSettingsView::class,
        'output' => [
            '0.name'    => OrderSettings::STATUS_ON_LABEL_CREATE,
            '0.label'   => 'settings_order_status_on_label_create',
            '0.options' => [],
            '0.type'    => 'SelectInput',
            '1.name'    => OrderSettings::STATUS_WHEN_LABEL_SCANNED,
            '1.label'   => 'settings_order_status_when_label_scanned',
            '1.options' => [],
            '1.type'    => 'SelectInput',
            '2.name'    => OrderSettings::STATUS_WHEN_DELIVERED,
            '2.label'   => 'settings_order_status_when_delivered',
            '2.options' => [],
            '2.type'    => 'SelectInput',
            '3.name'    => OrderSettings::IGNORE_ORDER_STATUSES,
            '3.label'   => 'settings_order_ignore_order_statuses',
            '3.type'    => 'CheckboxInput',
            '4.name'    => OrderSettings::ORDER_STATUS_MAIL,
            '4.label'   => 'settings_order_order_status_mail',
            '4.type'    => 'ToggleInput',
            '5.name'    => OrderSettings::SEND_NOTIFICATION_AFTER,
            '5.label'   => 'settings_order_send_notification_after',
            '5.options' => [],
            '5.type'    => 'SelectInput',
            '6.name'    => OrderSettings::SEND_ORDER_STATE_FOR_DIGITAL_STAMP,
            '6.label'   => 'settings_order_send_order_state_for_digital_stamp',
            '6.type'    => 'ToggleInput',
            '7.name'    => OrderSettings::SAVE_CUSTOMER_ADDRESS,
            '7.label'   => 'settings_order_save_customer_address',
            '7.type'    => 'ToggleInput',
            '8.name'    => OrderSettings::EMPTY_PARCEL_WEIGHT,
            '8.label'   => 'settings_order_save_customer_address',
            '8.type'    => 'TextInput',
            '9.name'    => OrderSettings::SAVE_CUSTOMER_ADDRESS,
            '9.label'   => 'settings_order_save_customer_address',
            '9.type'    => 'TextInput',
        ],
    ],
    'checkout settings' => [
        'class'  => CheckoutSettingsView::class,
        'output' => [
            '0.name'        => CheckoutSettings::USE_SEPARATE_ADDRESS_FIELDS,
            '0.label'       => 'settings_checkout_use_separate_address_fields',
            '0.type'        => 'ToggleInput',
            '1.name'        => CheckoutSettings::PRICE_TYPE,
            '1.label'       => 'settings_checkout_price_type',
            '1.options'     => [],
            '1.type'        => 'SelectInput',
            '2.name'        => CheckoutSettings::SHOW_DELIVERY_DAY,
            '2.label'       => 'settings_checkout_show_delivery_day',
            '2.type'        => 'ToggleInput',
            '3.name'        => CheckoutSettings::STRING_DELIVERY,
            '3.label'       => 'settings_checkout_string_delivery',
            '3.description' => 'settings_checkout_string_delivery_description',
            '3.type'        => 'TextInput',
            '4.name'        => CheckoutSettings::STRING_STANDARD_DELIVERY,
            '4.label'       => 'settings_checkout_string_standard_delivery',
            '4.description' => 'settings_checkout_string_standard_delivery_description',
            '4.type'        => 'TextInput',
            '5.name'        => CheckoutSettings::STRING_MORNING_DELIVERY,
            '5.label'       => 'settings_checkout_string_morning_delivery',
            '5.description' => 'settings_checkout_string_standard_delivery_description',
            '5.type'        => 'TextInput',
            '6.name'        => CheckoutSettings::STRING_EVENING_DELIVERY,
            '6.label'       => 'settings_checkout_string_evening_delivery',
            '6.description' => 'settings_checkout_string_standard_delivery_description',
            '6.type'        => 'TextInput',
            '7.name'        => CheckoutSettings::STRING_SATURDAY_DELIVERY,
            '7.label'       => 'settings_checkout_string_saturday_delivery',
            '7.description' => 'settings_checkout_string_standard_delivery_description',
            '7.type'        => 'TextInput',
            '8.name'        => CheckoutSettings::STRING_SIGNATURE,
            '8.label'       => 'settings_checkout_string_signature',
            '8.type'        => 'TextInput',
            '9.name'        => CheckoutSettings::STRING_ONLY_RECIPIENT,
            '9.label'       => 'settings_checkout_string_only_recipient',
            '9.type'        => 'TextInput',
            '10.name'       => CheckoutSettings::STRING_PICKUP,
            '10.label'      => 'settings_checkout_string_pickup',
            '10.type'       => 'TextInput',
            '11.name'       => CheckoutSettings::STRING_HOUSE_NUMBER,
            '11.label'      => 'settings_checkout_string_house_number',
            '11.type'       => 'TextInput',
            '12.name'       => CheckoutSettings::STRING_CITY,
            '12.label'      => 'settings_checkout_string_city',
            '12.type'       => 'TextInput',
            '13.name'       => CheckoutSettings::STRING_POSTAL_CODE,
            '13.label'      => 'settings_checkout_string_postal_code',
            '13.type'       => 'TextInput',
            '14.name'       => CheckoutSettings::STRING_COUNTRY,
            '14.label'      => 'settings_checkout_string_country',
            '14.type'       => 'TextInput',
            '15.name'       => CheckoutSettings::STRING_OPENING_HOURS,
            '15.label'      => 'settings_checkout_string_opening_hours',
            '15.type'       => 'TextInput',
            '16.name'       => CheckoutSettings::STRING_LOAD_MORE,
            '16.label'      => 'settings_checkout_string_load_more',
            '16.type'       => 'TextInput',
            '17.name'       => CheckoutSettings::STRING_PICKUP_LOCATIONS_MAP_BUTTON,
            '17.label'      => 'settings_checkout_string_pickup_locations_map_button',
            '17.type'       => 'TextInput',
            '18.name'       => CheckoutSettings::STRING_PICKUP_LOCATIONS_LIST_BUTTON,
            '18.label'      => 'settings_checkout_string_pickup_locations_list_button',
            '18.type'       => 'TextInput',
            '19.name'       => CheckoutSettings::STRING_RETRY,
            '19.label'      => 'settings_checkout_string_retry',
            '19.type'       => 'TextInput',
            '20.name'       => CheckoutSettings::STRING_ADDRESS_NOT_FOUND,
            '20.label'      => 'settings_checkout_string_address_not_found',
            '20.type'       => 'TextInput',
            '21.name'       => CheckoutSettings::STRING_WRONG_POSTAL_CODE_CITY,
            '21.label'      => 'settings_checkout_string_wrong_postal_code_city',
            '21.type'       => 'TextInput',
            '22.name'       => CheckoutSettings::STRING_WRONG_NUMBER_POSTAL_CODE,
            '22.label'      => 'settings_checkout_string_wrong_number_postal_code',
            '22.type'       => 'TextInput',
            '23.name'       => CheckoutSettings::STRING_FROM,
            '23.label'      => 'settings_checkout_string_from',
            '23.type'       => 'TextInput',
            '24.name'       => CheckoutSettings::STRING_DISCOUNT,
            '24.label'      => 'settings_checkout_string_discount',
            '24.type'       => 'TextInput',
        ],
    ],
    'customs settings'  => [
        'class'  => CustomsSettingsView::class,
        'output' => [
                '0.name'      => CustomsSettings::PACKAGE_CONTENTS,
                '0.label'     => 'settings_customs_package_contents',
                '0.options.1' => 'customs_package_contents_commercial_goods',
                '0.options.2' => 'customs_package_contents_commercial_samples',
                '0.options.3' => 'customs_package_contents_documents',
                '0.options.4' => 'customs_package_contents_gifts',
                '0.options.5' => 'customs_package_contents_return_shipment',
                '0.type'      => 'SelectInput',
                '1.name'      => CustomsSettings::CUSTOMS_CODE,
                '1.label'     => 'settings_customs_customs_code',
                '1.type'      => 'TextInput',
                '2.name'      => CustomsSettings::COUNTRY_OF_ORIGIN,
                '2.label'     => 'settings_customs_country_of_origin',
                '2.type'      => 'SelectInput',
            ] + addCountryOptions(2),
    ],
    'label settings'    => [
        'class'  => LabelSettingsView::class,
        'output' => [
            '0.name'             => LabelSettings::DESCRIPTION,
            '0.label'            => 'settings_label_description',
            '0.description'      => 'settings_label_description_description',
            '0.type'             => 'TextInput',
            '1.name'             => LabelSettings::FORMAT,
            '1.label'            => 'settings_label_format',
            '1.options.A4'       => 'settings_label_format_option_a4',
            '1.options.A6'       => 'settings_label_format_option_a6',
            '1.type'             => 'SelectInput',
            '2.name'             => LabelSettings::POSITION,
            '2.label'            => 'settings_label_position',
            '2.options.1'        => 'settings_label_position_option_1',
            '2.options.2'        => 'settings_label_position_option_2',
            '2.options.3'        => 'settings_label_position_option_3',
            '2.options.4'        => 'settings_label_position_option_4',
            '2.type'             => 'SelectInput',
            '3.name'             => LabelSettings::OUTPUT,
            '3.label'            => 'settings_label_output',
            '3.options.download' => 'settings_label_output_option_download',
            '3.options.print'    => 'settings_label_output_option_open',
            '3.type'             => 'SelectInput',
            '4.name'             => LabelSettings::PROMPT,
            '4.label'            => 'settings_label_prompt',
            '4.type'             => 'ToggleInput',
        ],
    ],
    'product settings'  => [
        'class'  => ProductSettingsView::class,
        'output' => [
                '0.name'                   => ProductSettings::ALLOW_ONLY_RECIPIENT,
                '0.label'                  => 'settings_product_allow_only_recipient',
                '0.type'                   => 'ToggleInput',
                '1.name'                   => ProductSettings::ALLOW_SIGNATURE,
                '1.label'                  => 'settings_product_allow_signature',
                '1.type'                   => 'ToggleInput',
                '2.name'                   => ProductSettings::COUNTRY_OF_ORIGIN,
                '2.label'                  => 'settings_product_country_of_origin',
                '2.type'                   => 'SelectInput',
                '3.name'                   => ProductSettings::CUSTOMS_CODE,
                '3.label'                  => 'settings_product_customs_code',
                '3.type'                   => 'TextInput',
                '4.name'                   => ProductSettings::DISABLE_DELIVERY_OPTIONS,
                '4.label'                  => 'settings_product_disable_delivery_options',
                '4.type'                   => 'ToggleInput',
                '5.name'                   => ProductSettings::DROP_OFF_DELAY,
                '5.label'                  => 'settings_product_drop_off_delay',
                '5.type'                   => 'TextInput',
                '6.name'                   => ProductSettings::EXPORT_AGE_CHECK,
                '6.label'                  => 'settings_product_export_age_check',
                '6.type'                   => 'ToggleInput',
                '7.name'                   => ProductSettings::EXPORT_INSURANCE,
                '7.label'                  => 'settings_product_export_insurance',
                '7.type'                   => 'ToggleInput',
                '8.name'                   => ProductSettings::EXPORT_LARGE_FORMAT,
                '8.label'                  => 'settings_product_export_large_format',
                '8.type'                   => 'ToggleInput',
                '9.name'                   => ProductSettings::FIT_IN_MAILBOX,
                '9.label'                  => 'settings_product_fit_in_mailbox',
                '9.type'                   => 'TextInput',
                '10.name'                  => ProductSettings::PACKAGE_TYPE,
                '10.label'                 => 'settings_product_package_type',
                '10.options.package'       => 'package_type_package',
                '10.options.mailbox'       => 'package_type_mailbox',
                '10.options.letter'        => 'package_type_letter',
                '10.options.digital_stamp' => 'package_type_digital_stamp',
                '10.type'                  => 'SelectInput',
                '11.name'                  => ProductSettings::RETURN_SHIPMENTS,
                '11.label'                 => 'settings_product_return_shipments',
                '11.type'                  => 'ToggleInput',
            ] + addCountryOptions(2),
    ],
]);

it('throws error when class is invalid', function () {
    class InvalidClassView extends AbstractView
    {
        protected function getFields(): Collection
        {
            return new Collection([['class' => 'TextInput']]);
        }
    }

    (new InvalidClassView())->toArray();
})->throws(InvalidArgumentException::class);

it('throws error when type is invalid', function () {
    class InvalidTypeView extends AbstractView
    {
        protected function getFields(): Collection
        {
            return new Collection([['type' => 'TextInput']]);
        }
    }

    (new InvalidTypeView())->toArray();
})->throws(InvalidArgumentException::class);
