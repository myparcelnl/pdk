<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Tests\Integration\Api\Response\BehatClientResponse;
use Symfony\Component\HttpFoundation\Request;

return [
    'match' => static function (Request $request): bool {
        return '/shipments' === $request->getPathInfo()
            && 'POST' === $request->getMethod();
    },

    'response' => static function (Request $request): ClientResponseInterface {
        $content     = json_decode($request->getContent(), true);
        $referenceId = Arr::get($content, 'data.shipments.0.reference_identifier');

        return new BehatClientResponse(
            json_encode([
                'data' => [
                    'ids' => [
                        [
                            'id'                   => 130400,
                            'reference_identifier' => $referenceId,
                        ],
                    ],
                ],
            ]), 200
        );
    },
];
