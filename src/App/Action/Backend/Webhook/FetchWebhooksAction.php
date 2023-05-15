<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Webhook;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FetchWebhooksAction extends AbstractWebhooksAction
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $subscriptions = $this->getExistingSubscriptions();

        return $this->createResponse($subscriptions);
    }
}
