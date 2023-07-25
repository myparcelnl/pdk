<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Settings\Model\CarrierSettings;

dataset('carrierExportSettings', [
    'export age check'      => [[CarrierSettings::EXPORT_AGE_CHECK => true]],
    'export large format'   => [[CarrierSettings::EXPORT_LARGE_FORMAT => true]],
    'export only recipient' => [[CarrierSettings::EXPORT_ONLY_RECIPIENT => true]],
    'export return'         => [[CarrierSettings::EXPORT_RETURN => true]],
    'export signature'      => [[CarrierSettings::EXPORT_SIGNATURE => true]],
    'export insurance'      => [
        [
            CarrierSettings::EXPORT_INSURANCE             => true,
            CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT => 0,
        ],
    ],
]);
