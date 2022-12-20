<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Request;

use MyParcelNL\Pdk\Base\Request\Request;

class PostWebhookSubscriptionRequest extends Request
{
    /**
     * @var string
     */
    private $hook;

    /**
     * @var string
     */
    private $url;

    /**
     * @param  string $hook
     * @param  string $url
     */
    public function __construct(string $hook, string $url)
    {
        parent::__construct();
        $this->hook = $hook;
        $this->url  = $url;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'webhook_subscriptions' => [
                    [
                        "hook" => $this->hook,
                        "url"  => $this->url,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return '/webhook_subscriptions';
    }
}
