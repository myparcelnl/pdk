<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use Symfony\Component\HttpFoundation\Response;

class ExamplePostShipmentsValidationErrorResponse extends ExampleJsonResponse
{
    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    /**
     * @return array[]
     */
    public function getContent(): array
    {
        return [
            'message'     => 'Shipment validation error (request_id: 1752047820.9951686e20ccf2f3b)',
            'request_id'  => '1752047820.9951686e20ccf2f3b',
            'status_code' => 422,
            'errors'      => [
                [
                    3705 => [
                        'fields' => [
                            'data.shipments[0].options.return',
                        ],
                        'human' => [
                            'data.shipments[0].options.return shipment option not supported',
                        ],
                    ],
                ],
            ],
        ];
    }
}
