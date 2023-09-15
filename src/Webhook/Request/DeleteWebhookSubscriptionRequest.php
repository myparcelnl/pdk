<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class DeleteWebhookSubscriptionRequest extends Request
{
    public $path = '/webhook_subscriptions/:id';

    public function __construct(private readonly int $id)
    {
        parent::__construct();
    }

    public function getMethod(): string
    {
        return 'DELETE';
    }

    public function getPath(): string
    {
        return strtr($this->path, [':id' => $this->id]);
    }
}
