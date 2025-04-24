<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Webhook;

use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
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

        // Try to unsubscribe from each webhook
        // If a webhook is owned by another shop (resourceOwnedByOthers), 
        // the unsubscribe method will handle it gracefully
        $subscriptions->each(function (WebhookSubscription $subscription) {
            $this->repository->unsubscribe($subscription->id);
        });

        // Remove all webhook subscriptions from local storage
        $this->pdkWebhooksRepository->store(new WebhookSubscriptionCollection());

        return $this->createResponse();
    }
}
