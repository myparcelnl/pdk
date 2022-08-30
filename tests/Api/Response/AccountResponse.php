<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class AccountResponse extends JsonResponse
{
    private const DEFAULT_ACCOUNTS = [
        [
            'platform_id' => 3,
            'id'          => 3,
            'shops'       => [
                [
                    'id'   => 3,
                    'name' => 'bloemkool',
                ],
            ],
        ],
    ];

    /**
     * @var array
     */
    private $accounts;

    public function __construct(
        array  $accounts = self::DEFAULT_ACCOUNTS,
        int    $status = 200,
        array  $headers = [],
               $body = null,
        string $version = '1.1',
        string $reason = null
    ) {
        parent::__construct($status, $headers, $body, $version, $reason);
        $this->accounts = $accounts;
    }

    public function getContent(): array
    {
        return [
            'data' => [
                'accounts' => $this->accounts,
            ],
        ];
    }
}
