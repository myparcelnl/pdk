<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryService;

return [
    'myparcel'     => [
        'id'           => 1,
        'localCountry' => CountryService::CC_NL,
    ],
    'sendmyparcel' => [
        'id'           => 2,
        'localCountry' => CountryService::CC_BE,
    ],
    'flespakket'   => [
        'id'           => 3,
        'localCountry' => CountryService::CC_NL,
    ],
];
