<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\HideSenderDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SameDayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\App\Options\Definition\TrackedDefinition;
use MyParcelNL\Pdk\App\Order\Calculator\General\AllowedInCarrierCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\CarrierSpecificCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\CustomerInformationCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\CustomsDeclarationCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\InsuranceCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\LabelDescriptionCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\PackageTypeCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\PackageTypeShipmentOptionsCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\TriStateOptionCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\WeightCalculator;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use function DI\factory;
use function DI\value;

/**
 * Business logic for data we cannot retrieve from the API (yet), and internal pdk logic.
 */
return [
    /**
     * API
     */

    // Shipments
    'customsCodeMaxLength'      => value(10),
    'dropOffDelayMaximum'       => value(14),
    'dropOffDelayMinimum'       => value(-1),
    'numberSuffixMaxLength'     => value(6),
    'packageTypeWeightLimits'   => value([
        DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME       => 2000,
        DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME => 2000,
        DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME => 2000,
    ]),
    'minimumWeight'             => value(1),

    // Delivery options
    'deliveryDaysWindowMin'     => value(0),
    'deliveryDaysWindowMax'     => value(14),
    'labelDescriptionMaxLength' => value(45),

    /**
     * PDK
     */

    'insurancePercentageMin'  => value(0),
    'insurancePercentageStep' => value(1),
    'insurancePercentageMax'  => value(100),

    /**
     * Options definitions
     */

    'orderOptionDefinitions' => factory(function (): array {
        return [
            new AgeCheckDefinition(),
            new DirectReturnDefinition(),
            new HideSenderDefinition(),
            new InsuranceDefinition(),
            new LargeFormatDefinition(),
            new OnlyRecipientDefinition(),
            new SameDayDeliveryDefinition(),
            new SignatureDefinition(),
            new TrackedDefinition(),
        ];
    }),

    'orderCalculators' => factory(function () {
        return [
            PackageTypeCalculator::class,
            TriStateOptionCalculator::class,
            AllowedInCarrierCalculator::class,
            PackageTypeShipmentOptionsCalculator::class,
            LabelDescriptionCalculator::class,
            InsuranceCalculator::class,
            CarrierSpecificCalculator::class,
            WeightCalculator::class,
            CustomerInformationCalculator::class,
            CustomsDeclarationCalculator::class,
        ];
    }),

    'digitalStampRanges'         => value([
        ['min' => 0, 'max' => 20, 'average' => 15],
        ['min' => 20, 'max' => 50, 'average' => 35],
        ['min' => 50, 'max' => 350, 'average' => 200],
        ['min' => 350, 'max' => 2000, 'average' => 1175],
    ]),

    //todo: kijken of hier iets mee moet gebeuren
    /**
     * - Off: The shipping method does not get delivery options. Default behavior and thus not saved in the model.
     * - [PackageTypeName]: The shipping method gets delivery options and the package type is fixed.
     */
    'allowedShippingMethodsKeys' => value([
        DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
        DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
        DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
    ]),
];
