<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use Symfony\Component\HttpFoundation\Request;

final class OrderStatusChangeWebhook extends AbstractHook
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function handle(Request $request): void
    {
        $content = $this->getHookBody($request);

        Actions::execute(PdkBackendActions::SYNCHRONIZE_ORDERS, [
            'orderIds' => $content['uuid'],
        ]);
    }
}
