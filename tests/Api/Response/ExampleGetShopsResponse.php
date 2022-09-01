<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetShopsResponse extends ExampleJsonResponse
{
    private const DEFAULT_SHOPS = [
        [
            'id'   => 3,
            'name' => 'creme fraiche',
        ],
    ];

    /**
     * @var array
     */
    private $shops;

    public function __construct(
        array  $shops = self::DEFAULT_SHOPS,
        int    $status = 200,
        array  $headers = [],
               $body = null,
        string $version = '1.1',
        string $reason = null
    ) {
        parent::__construct($status, $headers, $body, $version, $reason);
        $this->shops = $shops;
    }

    public function getContent(): array
    {
        return [
            'data' => [
                'shops' => $this->shops,
            ],
        ];
    }
}
