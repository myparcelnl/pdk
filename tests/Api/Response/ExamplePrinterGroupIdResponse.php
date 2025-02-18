<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use Symfony\Component\HttpFoundation\Response;

class ExamplePrinterGroupIdResponse extends ExampleJsonResponse
{
    public function getContent(): array
    {
        return [
            'results' => $this->responseContent ?? $this->getDefaultResponseContent(),
        ];
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_CREATED;
    }

    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [];
    }

}
