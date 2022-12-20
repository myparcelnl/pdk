<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Request;

use MyParcelNL\Pdk\Base\Request\Request;

class DeleteWebhookSubscriptionRequest extends Request
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
    public function getMethod(): string
    {
        return 'DELETE';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return strtr($this->path, [':id' => $this->id]);
    }
}
