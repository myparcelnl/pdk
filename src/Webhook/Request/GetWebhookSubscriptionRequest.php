<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class GetWebhookSubscriptionRequest extends Request
{
    public $path = '/webhook_subscriptions/:id';

    /**
     * @var int
     */
    private $id;

    /**
     * @param  int $id
     */
    public function __construct(int $id)
    {
        parent::__construct();
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return strtr($this->path, [':id' => $this->id]);
    }
}
