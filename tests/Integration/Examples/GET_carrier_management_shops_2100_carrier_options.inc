<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetCarrierOptionsResponse;
use MyParcelNL\Pdk\Tests\Integration\Api\Response\BehatClientResponse;
use Symfony\Component\HttpFoundation\Request;

return [
    'match' => static function (Request $request): bool {
        return '/carrier_management/shops/2100/carrier_options' === $request->getPathInfo()
            && 'GET' === $request->getMethod();
    },

    'response' => static function (): ClientResponseInterface {
        return BehatClientResponse::create(new ExampleGetCarrierOptionsResponse());
    },
];
