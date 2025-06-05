<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;use MyParcelNL\Pdk\Tests\Integration\Api\Response\BehatClientResponse;use Symfony\Component\HttpFoundation\Request;

return [
    'match' => static function (Request $request): bool {
        return '/printer-groups' === $request->getPathInfo()
            && 'GET' === $request->getMethod();
    },

    'response' => static function (Request $request): ClientResponseInterface {
        return new BehatClientResponse(json_encode([
    'message' => 'Unauthorized'
]), 401);
    },
];
