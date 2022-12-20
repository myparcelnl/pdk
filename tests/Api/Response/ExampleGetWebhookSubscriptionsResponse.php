<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetWebhookSubscriptionsResponse extends ExampleJsonResponse
{
    private const DEFAULT_WEBHOOKS = [
        [
            'hook' => 'christmas_tree',
            'url'  => 'https://example.com/webhook',
        ],
    ];

    /**
     * @var array
     */
    private $subscriptions;

    /**
     * @param  array       $subscriptions
     * @param  int         $status
     * @param  array       $headers
     * @param              $body
     * @param  string      $version
     * @param  string|null $reason
     */
    public function __construct(
        array  $subscriptions = self::DEFAULT_WEBHOOKS,
        int    $status = 200,
        array  $headers = [],
               $body = null,
        string $version = '1.1',
        string $reason = null
    ) {
        parent::__construct($status, $headers, $body, $version, $reason);
        $this->subscriptions = $subscriptions;
    }

    public function getContent(): array
    {
        return [
            'data' => [
                'webhook_subscriptions' => $this->subscriptions,
            ],
        ];
    }
}
