<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Webhook;

use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeleteWebhooksAction extends AbstractWebhooksAction
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $subscriptions = $this->getExistingSubscriptions();

        $subscriptions->each(function (WebhookSubscription $subscription) {
            $this->repository->unsubscribe($subscription->id);
        });

        return $this->createResponse();
    }
}
