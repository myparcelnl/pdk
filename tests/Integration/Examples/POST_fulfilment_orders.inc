<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrdersResponse;
use MyParcelNL\Pdk\Tests\Integration\Api\Response\BehatClientResponse;
use Symfony\Component\HttpFoundation\Request;

return [
    'match' => static function (Request $request): bool {
        return '/fulfilment/orders' === $request->getPathInfo()
            && 'POST' === $request->getMethod();
    },

    'response' => static function (): ClientResponseInterface {
        return BehatClientResponse::create(new ExamplePostOrdersResponse());
    },
];
