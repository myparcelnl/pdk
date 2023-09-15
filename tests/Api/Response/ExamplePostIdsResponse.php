<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use Symfony\Component\HttpFoundation\Response;

class ExamplePostIdsResponse extends ExampleJsonResponse
{
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

    protected function getResponseProperty(): string
    {
        return 'ids';
    }
}
