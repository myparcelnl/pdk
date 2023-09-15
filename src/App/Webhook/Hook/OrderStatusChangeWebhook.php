<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Symfony\Component\HttpFoundation\Request;

final class OrderStatusChangeWebhook extends AbstractHook
{
    public function handle(Request $request): void
    {
        $content = $this->getHookBody($request);

        Actions::execute(PdkBackendActions::SYNCHRONIZE_ORDERS, [
            'orderIds' => $content['uuid'],
        ]);
    }

    protected function getHookEvent(): string
    {
        return WebhookSubscription::ORDER_STATUS_CHANGE;
    }
}
