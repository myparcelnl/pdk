<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use Symfony\Component\HttpFoundation\Response;

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
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
