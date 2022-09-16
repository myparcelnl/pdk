<?php
/** @noinspection PhpUnhandledExceptionInspection, PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Settings\Model\DeliveryOptionsStringsSettings;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\View\AbstractView;
use MyParcelNL\Pdk\Settings\View\CarrierSettingsView;
use MyParcelNL\Pdk\Settings\View\CustomsSettingsView;
use MyParcelNL\Pdk\Settings\View\DeliveryOptionsStringsSettingsView;
use MyParcelNL\Pdk\Settings\View\GeneralSettingsView;
use MyParcelNL\Pdk\Settings\View\LabelSettingsView;
use MyParcelNL\Pdk\Settings\View\OrderSettingsView;
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
    'generalSettings'                => [
        'class'  => GeneralSettingsView::class,
        'output' => [
            '0.name'    => GeneralSettings::API_KEY,
            '0.label'   => 'Api Key',
            '0.type'    => 'TextInput',
            '1.name'    => GeneralSettings::ENABLE_API_LOGGING,
            '1.label'   => 'Api logging',
            '1.type'    => 'ToggleInput',
            '2.name'    => GeneralSettings::SHARE_CUSTOMER_INFORMATION,
            '2.label'   => 'Share customer information',
            '2.type'    => 'ToggleInput',
            '3.name'    => GeneralSettings::USE_SEPARATE_ADDRESS_FIELDS,
            '3.label'   => 'Use second address field',
            '3.type'    => 'ToggleInput',
            '4.name'    => GeneralSettings::CONCEPT_SHIPMENTS,
            '4.label'   => 'Turn on concept shipments',
            '4.type'    => 'ToggleInput',
            '5.name'    => GeneralSettings::ORDER_MODE,
            '5.label'   => 'Turn on order management',
            '5.type'    => 'ToggleInput',
            '6.name'    => GeneralSettings::PRICE_TYPE,
            '6.label'   => 'Price type display',
            '6.options' => [],
            '6.type'    => 'SelectInput',
            '7.name'    => GeneralSettings::TRACK_TRACE_EMAIL,
            '7.label'   => 'Track & trace in email',
            '7.type'    => 'ToggleInput',
            '8.name'    => GeneralSettings::TRACK_TRACE_MY_ACCOUNT,
            '8.label'   => 'Track & Trace in my account',
            '8.type'    => 'ToggleInput',
            '9.name'    => GeneralSettings::SHOW_DELIVERY_DAY,
            '9.label'   => 'Show delivery day to customer',
            '9.type'    => 'ToggleInput',
            '10.name'   => GeneralSettings::BARCODE_IN_NOTE,
            '10.label'  => 'Save barcode in note',
            '10.type'   => 'ToggleInput',
            '11.name'   => GeneralSettings::PROCESS_DIRECTLY,
            '11.label'  => 'Process orders automatically',
            '11.type'   => 'ToggleInput',
        ],
    ],
    'carrierSettings'                => [
        'class'  => CarrierSettingsView::class,
        'output' => [
            '0.name'        => CarrierSettings::ALLOW_DELIVERY_OPTIONS,
            '0.label'       => 'Allow delivery options',
            '0.type'        => 'ToggleInput',
            '1.name'        => CarrierSettings::ALLOW_EVENING_DELIVERY,
            '1.label'       => 'Allow evening delivery',
            '1.type'        => 'ToggleInput',
            '2.name'        => CarrierSettings::ALLOW_MONDAY_DELIVERY,
            '2.label'       => 'Allow monday delivery',
            '2.description' => 'Monday delivery is only possible when the package is delivered before 15.00 on Saturday at the designated PostNL locations. 
                 Note: To activate Monday delivery value 6 must be given with dropOffDays and value 1 must be given by monday_delivery. 
                 On Saturday the cutoffTime must be before 15:00 (14:30 recommended) so that Monday will be shown.',
            '2.type'        => 'ToggleInput',
            '3.name'        => CarrierSettings::ALLOW_MORNING_DELIVERY,
            '3.label'       => 'Allow morning delivery',
            '3.type'        => 'ToggleInput',
            '4.name'        => CarrierSettings::ALLOW_ONLY_RECIPIENT,
            '4.label'       => 'Allow only recipient',
            '4.type'        => 'ToggleInput',
            '5.name'        => CarrierSettings::ALLOW_PICKUP_LOCATIONS,
            '5.label'       => 'Allow pickup points',
            '5.type'        => 'ToggleInput',
            '6.name'        => CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
            '6.label'       => 'Allow same day delivery',
            '6.type'        => 'ToggleInput',
            '7.name'        => CarrierSettings::ALLOW_SATURDAY_DELIVERY,
            '7.label'       => 'Allow saturday delivery',
            '7.type'        => 'ToggleInput',
            '8.name'        => CarrierSettings::ALLOW_SIGNATURE,
            '8.label'       => 'Allow signature',
            '8.type'        => 'ToggleInput',
            '9.name'        => 'dropOffPossibilities',
            '9.label'       => 'Drop off days',
            '9.type'        => 'DropOffDaySelectInput',
            '10.name'       => 'featureShowDeliveryDate',
            '10.label'      => 'Show delivery date',
            '10.type'       => 'ToggleInput',
            '11.name'       => 'priceEveningDelivery',
            '11.label'      => 'Price evening delivery',
            '11.type'       => 'TextInput',
            '12.name'       => 'priceMorningDelivery',
            '12.label'      => 'Price morning delivery',
            '12.type'       => 'TextInput',
            '13.name'       => 'priceOnlyRecipient',
            '13.label'      => 'Price only recipient',
            '13.type'       => 'TextInput',
            '14.name'       => 'pricePackageTypeDigitalStamp',
            '14.label'      => 'Price package type digital stamp',
            '14.type'       => 'TextInput',
            '15.name'       => 'pricePackageTypeMailbox',
            '15.label'      => 'Price package type mailbox',
            '15.type'       => 'TextInput',
            '16.name'       => 'pricePickup',
            '16.label'      => 'Price pickup',
            '16.type'       => 'TextInput',
            '17.name'       => 'priceSameDayDelivery',
            '17.label'      => 'Price same day delivery',
            '17.type'       => 'TextInput',
            '18.name'       => 'priceSignature',
            '18.label'      => 'Price signature',
            '18.type'       => 'TextInput',
            '19.name'       => 'priceStandardDelivery',
            '19.label'      => 'Price standard delivery',
            '19.type'       => 'TextInput',
            '20.name'       => 'defaultPackageType',
            '20.label'      => 'Default package type',
            '20.type'       => 'SelectInput',
            '21.name'       => 'exportAgeCheck',
            '21.label'      => 'Age check 18+',
            '21.type'       => 'ToggleInput',
            '22.name'       => 'exportInsured',
            '22.label'      => 'Insure shipment',
            '22.type'       => 'ToggleInput',
            '23.name'       => 'exportInsuredAmount',
            '23.label'      => 'Insure from price',
            '23.type'       => 'TextInput',
            '24.name'       => 'exportInsuredAmountMax',
            '24.label'      => 'Insure to maximum',
            '24.type'       => 'SelectInput',
            '25.name'       => 'exportInsuredForBe',
            '25.label'      => 'Insure shipments to Belgium',
            '25.type'       => 'ToggleInput',
            '26.name'       => 'exportExtraLargeFormat',
            '26.label'      => 'Extra large package',
            '26.type'       => 'ToggleInput',
            '27.name'       => 'exportOnlyRecipient',
            '27.label'      => 'Only home address',
            '27.type'       => 'ToggleInput',
            '28.name'       => 'exportReturnShipments',
            '28.label'      => 'Return when not delivered',
            '28.type'       => 'ToggleInput',
            '29.name'       => 'exportSignature',
            '29.label'      => 'Signature before delivery',
            '29.type'       => 'ToggleInput',
            '30.name'       => 'digitalStampDefaultWeight',
            '30.label'      => 'Default weight digital stamp',
            '30.type'       => 'SelectInput',
        ],
    ],
    'orderSettings'                  => [
        'class'  => OrderSettingsView::class,
        'output' => [
            '0.name'    => OrderSettings::STATUS_ON_LABEL_CREATE,
            '0.label'   => 'Order status when label created',
            '0.options' => [],
            '0.type'    => 'SelectInput',
            '1.name'    => OrderSettings::STATUS_WHEN_LABEL_SCANNED,
            '1.label'   => 'Order status when label scanned',
            '1.options' => [],
            '1.type'    => 'SelectInput',
            '2.name'    => OrderSettings::STATUS_WHEN_DELIVERED,
            '2.label'   => 'Order status when delivered',
            '2.options' => [],
            '2.type'    => 'SelectInput',
            '3.name'    => OrderSettings::IGNORE_ORDER_STATUSES,
            '3.label'   => 'Ignore order statuses',
            '3.type'    => 'CheckboxInput',
            '4.name'    => OrderSettings::ORDER_STATUS_MAIL,
            '4.label'   => 'Order status mail',
            '4.type'    => 'ToggleInput',
            '5.name'    => OrderSettings::SEND_NOTIFICATION_AFTER,
            '5.label'   => 'Send notification after',
            '5.options' => [],
            '5.type'    => 'SelectInput',
            '6.name'    => OrderSettings::SEND_ORDER_STATE_FOR_DIGITAL_STAMPS,
            '6.label'   => 'Automatic set order state to sent for digital stamp',
            '6.type'    => 'ToggleInput',
        ],
    ],
    'deliveryOptionsStringsSettings' => [
        'class'  => DeliveryOptionsStringsSettingsView::class,
        'output' => [
            '0.name'        => DeliveryOptionsStringsSettings::DELIVERY_TITLE,
            '0.label'       => 'Delivery Title',
            '0.description' => 'Title of the delivery option.',
            '0.type'        => 'TextInput',
            '1.name'        => DeliveryOptionsStringsSettings::STANDARD_DELIVERY_TITLE,
            '1.label'       => 'Standard delivery title',
            '1.description' => 'When there is no title, the delivery time will automatically be visible',
            '1.type'        => 'TextInput',
            '2.name'        => DeliveryOptionsStringsSettings::MORNING_DELIVERY_TITLE,
            '2.label'       => 'Morning delivery title',
            '2.description' => 'When there is no title, the delivery time will automatically be visible',
            '2.type'        => 'TextInput',
            '3.name'        => DeliveryOptionsStringsSettings::EVENING_DELIVERY_TITLE,
            '3.label'       => 'Evening delivery title',
            '3.description' => 'When there is no title, the delivery time will automatically be visible',
            '3.type'        => 'TextInput',
            '4.name'        => DeliveryOptionsStringsSettings::SATURDAY_DELIVERY_TITLE,
            '4.label'       => 'Saturday delivery title',
            '4.description' => 'When there is no title, the delivery time will automatically be visible',
            '4.type'        => 'TextInput',
            '5.name'        => DeliveryOptionsStringsSettings::SIGNATURE_TITLE,
            '5.label'       => 'Signature title',
            '5.type'        => 'TextInput',
            '6.name'        => DeliveryOptionsStringsSettings::ONLY_RECIPIENT_TITLE,
            '6.label'       => 'Only recipient title',
            '6.type'        => 'TextInput',
            '7.name'        => DeliveryOptionsStringsSettings::PICKUP_TITLE,
            '7.label'       => 'Pickup title',
            '7.type'        => 'TextInput',
            '8.name'        => DeliveryOptionsStringsSettings::HOUSE_NUMBER,
            '8.label'       => 'House number text',
            '8.type'        => 'TextInput',
            '9.name'        => DeliveryOptionsStringsSettings::CITY,
            '9.label'       => 'City text',
            '9.type'        => 'TextInput',
            '10.name'       => DeliveryOptionsStringsSettings::POSTCODE,
            '10.label'      => 'Postal code text',
            '10.type'       => 'TextInput',
            '11.name'       => DeliveryOptionsStringsSettings::CC,
            '11.label'      => 'Country text',
            '11.type'       => 'TextInput',
            '12.name'       => DeliveryOptionsStringsSettings::OPENING_HOURS,
            '12.label'      => 'Opening hours text',
            '12.type'       => 'TextInput',
            '13.name'       => DeliveryOptionsStringsSettings::LOAD_MORE,
            '13.label'      => 'Load more title',
            '13.type'       => 'TextInput',
            '14.name'       => DeliveryOptionsStringsSettings::PICKUP_LOCATIONS_MAP_BUTTON,
            '14.label'      => 'Pickup map button title',
            '14.type'       => 'TextInput',
            '15.name'       => DeliveryOptionsStringsSettings::PICKUP_LOCATIONS_LIST_BUTTON,
            '15.label'      => 'Pickup list button text',
            '15.type'       => 'TextInput',
            '16.name'       => DeliveryOptionsStringsSettings::RETRY,
            '16.label'      => 'Retry title',
            '16.type'       => 'TextInput',
            '17.name'       => DeliveryOptionsStringsSettings::ADDRESS_NOT_FOUND,
            '17.label'      => 'Address not found title',
            '17.type'       => 'TextInput',
            '18.name'       => DeliveryOptionsStringsSettings::WRONG_POSTAL_CODE_CITY,
            '18.label'      => 'Wrong postal code/city combination title',
            '18.type'       => 'TextInput',
            '19.name'       => DeliveryOptionsStringsSettings::WRONG_NUMBER_POSTAL_CODE,
            '19.label'      => 'Wrong number/postal code title',
            '19.type'       => 'TextInput',
            '20.name'       => DeliveryOptionsStringsSettings::FROM,
            '20.label'      => 'From title',
            '20.type'       => 'TextInput',
            '21.name'       => DeliveryOptionsStringsSettings::DISCOUNT,
            '21.label'      => 'Discount title',
            '21.type'       => 'TextInput',
        ],
    ],
    'customsSettings'                => [
        'class'  => CustomsSettingsView::class,
        'output' => [
                '0.name'      => 'defaultPackageContents',
                '0.label'     => 'Package contents',
                '0.options.1' => 'Commercial goods',
                '0.options.2' => 'Commercial samples',
                '0.options.3' => 'Documents',
                '0.options.4' => 'Gifts',
                '0.options.5' => 'Return shipment',
                '0.type'      => 'SelectInput',
                '1.name'      => CustomsSettings::DEFAULT_CUSTOMS_CODE,
                '1.label'     => 'Default customs code',
                '1.type'      => 'TextInput',
                '2.name'      => CustomsSettings::DEFAULT_COUNTRY_OF_ORIGIN,
                '2.label'     => 'Default country of origin',
                '2.type'      => 'SelectInput',
            ] + addCountryOptions(2),
    ],
    'labelSettings'                  => [
        'class'  => LabelSettingsView::class,
        'output' => [
            '0.name'       => LabelSettings::LABEL_DESCRIPTION,
            '0.label'      => 'Label description',
            '0.desc'       => 'The maximum length is 45 characters. You can add the following variables to the description',
            '0.type'       => 'TextInput',
            '1.name'       => LabelSettings::LABEL_SIZE,
            '1.label'      => 'Default label size',
            '1.options.a4' => 'A4',
            '1.options.a6' => 'A6',
            '1.type'       => 'SelectInput',
            '2.name'       => LabelSettings::DEFAULT_POSITION,
            '2.label'      => 'Default label position',
            '2.options.1'  => 'Top left',
            '2.options.2'  => 'Top right',
            '2.options.3'  => 'Bottom left',
            '2.options.4'  => 'Bottom right',
            '2.type'       => 'SelectInput',
            '3.name'       => LabelSettings::LABEL_OPEN_DOWNLOAD,
            '3.label'      => 'Open or download label',
            '3.options.1'  => 'Open',
            '3.options.0'  => 'Download',
            '3.type'       => 'SelectInput',
            '4.name'       => LabelSettings::PROMPT_POSITION,
            '4.label'      => 'Prompt for label position',
            '4.type'       => 'ToggleInput',
        ],
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
