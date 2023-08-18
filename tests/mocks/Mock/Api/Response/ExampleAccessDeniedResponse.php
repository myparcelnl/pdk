<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Api\Response;

use Symfony\Component\HttpFoundation\Response;

final class ExampleAccessDeniedResponse extends ExampleJsonResponse
{
    /**
     * @return array
     */
    public function getContent(): array
    {
        return [
            'message'    => 'Access Denied. (request_id: 1691398492.036364d0b15c08dd4)',
            'request_id' => '1691398492.036364d0b15c08dd4',
            'errors'     => [
                [
                    'status'  => 401,
                    'code'    => 3000,
                    'title'   => 'Access Denied.',
                    'message' => 'Access Denied.',
                ],
            ],
        ];
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }
}
