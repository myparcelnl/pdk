<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
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
        if (\array_key_exists('shipment_reference_identifier', $content) && '' !== trim($content['shipment_reference_identifier'])) {
            $orderIds = [trim($content['shipment_reference_identifier'])];
        }

        // when the webhook is received from order mode
        if (\array_key_exists('order_id', $content) && '' !== trim($content['order_id'])) {
            // translate order_id (which is api identifier / uuid) to local order id for db
            $order    = Pdk::get(PdkOrderRepositoryInterface::class)
                ->getByApiIdentifier(trim($content['order_id']));
            if ($order) {
                $externalIdentifier = $order->getExternalIdentifier();

                if (null !== $externalIdentifier && '' !== $externalIdentifier) {
                    $orderIds = [$externalIdentifier];
                }
            }
        }

        if (empty($orderIds)) {
            Logger::debug('Skipping shipment status change webhook without a valid order identifier', [
                'shipment_id'                   => $content['shipment_id'] ?? null,
                'order_id'                      => $content['order_id'] ?? null,
                'shipment_reference_identifier' => $content['shipment_reference_identifier'] ?? null,
            ]);

            return;
        }

        Actions::execute(PdkBackendActions::UPDATE_SHIPMENTS, [
            'orderIds'                      => $orderIds,
            'shipmentIds'                   => [$content['shipment_id']],
            'orderStatus'                   => OrderSettings::getStatus((int)($content['status'] ?? null)),
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
