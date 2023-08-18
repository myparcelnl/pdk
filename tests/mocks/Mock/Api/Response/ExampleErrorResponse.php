<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Api\Response;

use Symfony\Component\HttpFoundation\Response;

class ExampleErrorResponse extends ExampleJsonResponse
{
    public function getContent(): array
    {
        return [
            'message'    => 'Could not get pickup locations (request_id: 1661966070.7928630f96f6c18e7)',
            'request_id' => '1661966070.7928630f96f6c18e7',
            'errors'     => [
                [
                    'code'    => 3212,
                    'message' => 'cc is required',
                ],
                [
                    'code'    => 3212,
                    'message' => 'postal_code is required',
                ],
                [
                    'code'    => 3212,
                    'message' => 'number is required',
                ],
            ],
        ];
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
