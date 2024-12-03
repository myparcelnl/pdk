<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Symfony\Component\HttpFoundation\Request;

final class ShipmentStatusChangeWebhook extends AbstractHook
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function handle(Request $request): void
    {
        $content = $this->getHookBody($request);

        // translate order_id (which is api uuid) to local order id for db
        // wrap in try catch to be able to log what’s going on
        if (! is_int($content['order_id'])) {
            $repo = Pdk::get(PdkOrderRepositoryInterface::class);
            $order = $repo->query(['uuid' => $content['order_id']]);
            $content['order_id'] = $order->getId();
        }

        Actions::execute(PdkBackendActions::UPDATE_SHIPMENTS, [
            'orderIds'    => [$content['order_id']],
            'shipmentIds' => [$content['shipment_id']],
        ]);
    }

    /**
     * @return string
     */
    protected function getHookEvent(): string
    {
        return WebhookSubscription::SHIPMENT_STATUS_CHANGE;
    }
}
