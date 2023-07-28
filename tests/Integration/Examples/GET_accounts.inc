<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetAccountsResponse;
use MyParcelNL\Pdk\Tests\Integration\Bootstrap\BehatClientResponse;
use Symfony\Component\HttpFoundation\Request;

return [
    'match' => static function (Request $request): bool {
        return '/accounts' === $request->getPathInfo()
            && 'GET' === $request->getMethod();
    },

    'response' => static function (Request $request): ClientResponseInterface {
        return BehatClientResponse::create(new ExampleGetAccountsResponse());
    },
];
