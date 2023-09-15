<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Symfony\Component\HttpFoundation\Request;

final class SubscriptionCreatedOrUpdatedWebhook extends AbstractHook
{
    public function handle(Request $request): void
    {
        Actions::execute(PdkBackendActions::UPDATE_SUBSCRIPTION_FEATURES);
    }

    protected function getHookEvent(): string
    {
        return WebhookSubscription::SUBSCRIPTION_CREATED_OR_UPDATED;
    }
}
