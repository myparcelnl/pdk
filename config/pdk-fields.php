<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;

use function DI\factory;
use function DI\value;

/**
 * Fields that are used in the app.
 */
return [
    /**
     * Default fields
     */
    'fieldAddress1'   => value('address_1'),
    'fieldAddress2'   => value('address_2'),
    'fieldCity'       => value('city'),
    'fieldCompany'    => value('company'),
    'fieldCountry'    => value('country'),
    'fieldEmail'      => value('email'),
    'fieldFirstName'  => value('first_name'),
    'fieldLastName'   => value('last_name'),
    'fieldPhone'      => value('phone'),
    'fieldPostalCode' => value('postal_code'),
    'fieldState'      => value('state'),

    /**
     * Custom fields for separate address fields.
     */
    'fieldNumber'       => value('number'),
    'fieldNumberSuffix' => value('number_suffix'),
    'fieldStreet'       => value('street'),

    /**
     * Custom fields for tax fields
     */
    'fieldEoriNumber' => value('eori_number'),
    'fieldVatNumber'  => value('vat_number'),

    'defaultFields' => value([
        'fieldAddress1',
        'fieldAddress2',
        'fieldCity',
        'fieldCompany',
        'fieldCountry',
        'fieldEmail',
        'fieldFirstName',
        'fieldLastName',
        'fieldPhone',
        'fieldPostalCode',
        'fieldState',
    ]),

    'separateAddressFields' => value([
        'fieldNumber',
        'fieldNumberSuffix',
        'fieldStreet',
    ]),

    // Replacable fields for the address widget
    'addressFields' => value([
        'fieldAddress1',
        'fieldAddress2',
        'fieldCity',
        // 'fieldCountry', currently disabled - address widget is NL-only and country selection is not happening within it.
        'fieldPostalCode',
        'fieldState',
        'fieldNumber',
        'fieldNumberSuffix',
        'fieldStreet',
    ]),

    'taxFields' => value([
        'fieldEoriNumber',
        'fieldVatNumber',
    ]),

    'allFields' => value([
        'fieldAddress1',
        'fieldAddress2',
        'fieldCity',
        'fieldCompany',
        'fieldCountry',
        'fieldEmail',
        'fieldEoriNumber',
        'fieldFirstName',
        'fieldLastName',
        'fieldNumber',
        'fieldNumberSuffix',
        'fieldPhone',
        'fieldPostalCode',
        'fieldState',
        'fieldStreet',
        'fieldVatNumber',
    ]),

    /**
     * Carriers that need tax fields.
     */
    'carriersWithTaxFields' => value([
        'dhleuroplus',
    ]),

    /**
     * Countries that support separate address fields.
     */
    'countriesWithSeparateAddressFields' => value([
        CountryCodes::CC_NL,
        CountryCodes::CC_BE,
    ]),

    /**
     * The name of the hidden input in the checkout where delivery options are stored.
     * TODO: Rename to "deliveryOptionsHiddenInputName" in the next major.
     */
    'checkoutHiddenInputName' => factory(function () {
        return sprintf('%s_checkout_data', PdkFacade::getAppInfo()->name);
    }),

    /**
     * The name of the hidden input in the checkout where address data is stored.
     */
    'checkoutAddressHiddenInputName' => factory(function () {
        return sprintf('%s_checkout_address', PdkFacade::getAppInfo()->name);
    }),
];
