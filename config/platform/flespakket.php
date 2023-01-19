<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

return [
    'name'             => 'flespakket',
    'human'            => 'Flespakket',
    'localCountry'     => CountryService::CC_NL,
    'defaultCarrier'   => Carrier::CARRIER_POSTNL_NAME,
    'defaultCarrierId' => Carrier::CARRIER_POSTNL_ID,
];
