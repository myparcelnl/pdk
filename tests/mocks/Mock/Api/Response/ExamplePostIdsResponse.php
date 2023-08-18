<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Api\Response;

use Symfony\Component\HttpFoundation\Response;

class ExamplePostIdsResponse extends ExampleJsonResponse
{
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
        return [
            ['id' => 1],
        ];
    }

    /**
     * @return string
     */
    protected function getResponseProperty(): string
    {
        return 'ids';
    }
}
