<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use Symfony\Component\HttpFoundation\Response;

class ExamplePostShipmentsResponse extends ExampleJsonResponse
{
    private const DEFAULT_IDS = [
        ['id' => 1],
    ];

    /**
     * @var array|int[]
     */
    private $ids;

    public function __construct(
        array  $ids = self::DEFAULT_IDS,
        int    $status = 200,
        array  $headers = [],
               $body = null,
        string $version = '1.1',
        string $reason = null
    ) {
        parent::__construct($status, $headers, $body, $version, $reason);
        $this->ids = $ids;
    }

    /**
     * @return array[]
     */
    public function getContent(): array
    {
        return [
            'data' => [
                'ids' => $this->ids,
            ],
        ];
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_CREATED;
    }
}
