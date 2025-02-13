<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use Symfony\Component\HttpFoundation\Response;

class ExamplePrinterGroupIdResponse extends ExampleJsonResponse
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
            [
                'id' => '55b53b20-91aa-4a53-8bb2-c4c120df9921',
                'name'=>'Test name',
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getResponseProperty(): string
    {
        return 'results';
    }
}
