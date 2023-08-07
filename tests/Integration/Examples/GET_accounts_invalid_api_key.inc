<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleAccessDeniedResponse;
use MyParcelNL\Pdk\Tests\Integration\Api\Response\BehatClientResponse;
use Symfony\Component\HttpFoundation\Request;

return [
    'match' => static function (Request $request): bool {
        return '/accounts' === $request->getPathInfo()
            && 'GET' === $request->getMethod()
            && 'invalid-api-key' === $request->headers->get('X-Api-Key');
    },

    'response' => static function (): ClientResponseInterface {
        return BehatClientResponse::create(new ExampleAccessDeniedResponse());
    },
];
