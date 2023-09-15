<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Webhook;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FetchWebhooksAction extends AbstractWebhooksAction
{
    public function handle(Request $request): Response
    {
        $subscriptions = $this->getExistingSubscriptions();

        return $this->createResponse($subscriptions);
    }
}
