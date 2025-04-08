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
        $content  = $this->getHookBody($request);
        $orderIds = [];

        // when the webhook is received from shipment mode
        if (isset($content['shipment_reference_identifier'])) {
            $orderIds = [$content['shipment_reference_identifier']];
        }

        // when the webhook is received from order mode
        if (isset($content['order_id'])) {
            // translate order_id (which is api identifier / uuid) to local order id for db
            $order    = Pdk::get(PdkOrderRepositoryInterface::class)
                ->getByApiIdentifier($content['order_id']);
            if ($order) {
                $orderIds = [$order->getExternalIdentifier()];
            }
        }

        Actions::execute(PdkBackendActions::UPDATE_SHIPMENTS, [
            'orderIds'                      => $orderIds,
            'shipmentIds'                   => [$content['shipment_id']],
            'linkFirstShipmentToFirstOrder' => true,
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
