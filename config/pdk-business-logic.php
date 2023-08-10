<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
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
    'dropOffDelayMinimum'     => value(0),
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

    'insuranceFactorMin'  => value(0.0),
    'insuranceFactorStep' => value(0.01),
    'insuranceFactorMax'  => value(1.0),
];
