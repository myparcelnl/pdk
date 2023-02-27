<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;

return [
    'name'           => 'myparcel',
    'human'          => 'MyParcel',
    'localCountry'   => CountryCodes::CC_NL,
    'defaultCarrier' => CarrierOptions::CARRIER_POSTNL_NAME,
];
