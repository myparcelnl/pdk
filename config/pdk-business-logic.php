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
use MyParcelNL\Pdk\App\Order\Calculator\General\AgeCheckCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\AllowedInCarrierCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\CarrierSpecificCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\InsuranceCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\LabelDescriptionCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\PackageTypeCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\PackageTypeShipmentOptionsCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\TriStateOptionCalculator;
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
    'customsCodeMaxLength'    => value(10),
    'dropOffDelayMaximum'     => value(14),
    'dropOffDelayMinimum'     => value(-1),
    'numberSuffixMaxLength'   => value(6),
    'packageTypeWeightLimits' => value([
        DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME       => 2000,
        DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME => 2000,
    ]),

    // Delivery options
    'deliveryDaysWindowMin'   => value(0),
    'deliveryDaysWindowMax'   => value(14),

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
        ];
    }),
];
