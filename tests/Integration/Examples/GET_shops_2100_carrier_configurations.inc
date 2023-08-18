<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Api\Response\BehatClientResponse;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetCarrierConfigurationResponse;
use Symfony\Component\HttpFoundation\Request;

return [
    'match' => static function (Request $request): bool {
        return '/shops/2100/carrier_configurations' === $request->getPathInfo()
            && 'GET' === $request->getMethod();
    },

    'response' => static function (): ClientResponseInterface {
        return BehatClientResponse::create(new ExampleGetCarrierConfigurationResponse());
    },
];
