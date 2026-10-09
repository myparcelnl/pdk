<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * The response of GET /pdfs/:hash while the bulk labels pdf is still being generated.
 */
class ExampleGetLabelPdfNotReadyResponse extends ExampleJsonResponse
{
    public function getContent(): array
    {
        return [
            'message'     => 'Bestand nog niet gereed en/of niet-bestaand (request_id: 1790604849.14146aba763122865)',
            'request_id'  => '1790604849.14146aba763122865',
            'status_code' => Response::HTTP_NOT_FOUND,
            'errors'      => [
                [
                    'code'   => 5100,
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ],
        ];
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
