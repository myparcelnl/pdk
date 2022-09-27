<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;

return [
    'name'           => 'flespakket',
    'human'          => 'Flespakket',
    'localCountry'   => CountryService::CC_NL,
    'defaultCarrier' => CarrierOptions::CARRIER_POSTNL_NAME,
];
