<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use MyParcelNL\Pdk\Base\Http\ResponseCodes;

class MyParcelApiErrorResponse extends JsonResponse
{
    public function getContent(): array
    {
        return [
            'errors' => [
                [
                    'field'   => 'bloemkool',
                    'message' => 'verrot',
                ],
            ],
        ];
    }

    public function getStatusCode(): int
    {
        return ResponseCodes::HTTP_UNPROCESSABLE_ENTITY;
    }
}
