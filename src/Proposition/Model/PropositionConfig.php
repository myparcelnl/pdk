<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Proposition\Collection\PropositionWeightCategoriesCollection;

/**
 * The proposition config is used to define individual MyParcel propositions
 * and their configurations. This is used to determine which features are available.
 *
 * It includes information used both by the backoffice and plugins/PDK,
 *  therefore not all defined properties are relevant for the PDK and may not be applied.
 *
 * @property PropositionMetadata $proposition
 * @property array $applications
 * @property string $countryCode
 * @property array $subscriptions
 * @property array $internationalization
 * @property array $consumerPortal
 * @property array $retailLocations
 * @property array $billing
 * @property PropositionContracts $contracts
 * @property array $paymentProvider
 * @property array $companyDetails
 * @property array $centerOfCountry
 * @property array $assets
 * @property array $exampleDocuments
 * @property string[] $easyReturnServiceCountryCodes
 * @property string[] $allowedReturnCountryCodes
 * @property PropositionRulesConfig $rules
 * @property PropositionWeightCategoriesCollection $weightCategories
 * @property bool $enablePalletSupport
 * @property bool $enablePrinterlessReturn
 * @property bool $enableLogoOnLabel
 * @property bool $enableShipmentEstimate
 * @property bool $enableFeedbackModal
 * @property bool $enableCompanyDetailsEdit
 * @property bool $enableChamberOfCommerceSupport
 * @property bool $enableVATSupport
 * @property bool $enableMyParcelWebShop
 * @property bool $enableImportExport
 * @property bool $requiresEmailRecipient
 * @property array $support
 * @property array $bankMandate
 * @property array $integrations
 * @property array $content
 * @property array $abTesting
 * @property array $tools
 * @package MyParcelNL\Pdk\Proposition
 */
class PropositionConfig extends Model
{
    // Define the properties and methods for the PropositionConfig class

    protected $attributes = [
        'proposition' => PropositionMetadata::class,
        'applications' => null,
        'countryCode' => null,
        'subscriptions' => null,
        'internationalization' => PropositionI18nConfig::class,
        'consumerPortal' => null,
        'retailLocations' => null,
        'billing' => null,
        'contracts' => PropositionContracts::class, // This contains the carrier information
        'paymentProvider' => null,
        'companyDetails' => null,
        'centerOfCountry' => null,
        'assets' => null,
        'exampleDocuments' => null,
        'easyReturnServiceCountryCodes' => [],
        'allowedReturnCountryCodes' => [],

        // Rules for country- or packageType-specific requirements
        'rules' => PropositionRulesConfig::class,

        'weightCategories' => PropositionWeightCategoriesCollection::class,

        // Proposition-level features
        'enablePalletSupport' => false,
        'enablePrinterlessReturn' => false,
        'enableLogoOnLabel' => null,
        'enableShipmentEstimate' => null,
        'enableFeedbackModal' => null,
        'enableCompanyDetailsEdit' => null,
        'enableChamberOfCommerceSupport' => null,
        'enableVATSupport' => null,
        'enableMyParcelWebShop' => null,
        'enableImportExport' => null,

        // Not currently relevant for PDK / integrations
        'requiresEmailRecipient' => null,
        'support' => null,
        'bankMandate' => null,
        'integrations' => null,
        'content' => null,
        'abTesting' => null,
        'tools' => null,
    ];

    protected $casts = [
        'proposition' => PropositionMetadata::class,
        'applications' => 'array',
        'countryCode' => 'string', // Should be an enum of country codes, but this is not yet possible in PHP 7.4 (localCountry)
        'subscriptions' => 'array',
        'internationalization' => 'array',
        'consumerPortal' => 'array',
        'retailLocations' => 'array',
        'billing' => 'array',
        'contracts' => PropositionContracts::class, // contains "available" which is a PropositionContractCollection
        'paymentProvider' => 'array',
        'companyDetails' => 'array',
        'centerOfCountry' => 'array',
        'assets' => 'array',
        'exampleDocuments' => 'array',
        'easyReturnServiceCountryCodes' => 'array',
        'allowedReturnCountryCodes' => 'array',
        // Rules are arrays of country-specific configurations
        'rules' => PropositionRulesConfig::class,
        // Proposition-level features
        'enablePalletSupport' => 'boolean',
        'enablePrinterlessReturn' => 'boolean',
        'enableLogoOnLabel' => 'boolean',
        'enableShipmentEstimate' => 'boolean',
        'enableFeedbackModal' => 'boolean',
        'enableCompanyDetailsEdit' => 'boolean',
        'enableChamberOfCommerceSupport' => 'boolean',
        'enableVATSupport' => 'boolean',
        'enableMyParcelWebShop' => 'boolean',
        'enableImportExport' => 'boolean',
        // Not currently relevant for PDK / integrations
        'requiresEmailRecipient' => 'boolean',
        'support' => 'array',
        'bankMandate' => 'array',
        'integrations' => 'array',
        'content' => 'array',
        'abTesting' => 'array',
        'weightCategories' => PropositionWeightCategoriesCollection::class,
        'tools' => 'array',
    ];
}
