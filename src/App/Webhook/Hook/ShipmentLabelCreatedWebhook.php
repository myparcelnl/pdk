<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Symfony\Component\HttpFoundation\Request;

final class ShipmentLabelCreatedWebhook extends AbstractHook
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function handle(Request $request): void
    {
        // @TODO this webhook implementation below was broken, as the request payload for this webhook does not contain either "shipment_reference_identifier" or "shipment_id".
        // This is an effective no-open to prevent unintended side-effects and since this webhook is always registered by the plugin, we cannot remove it easily.
        // @SEE https://developer.myparcel.nl/api-reference/11.webhook-object-definitions.html#_11-c
        return;
        // $content = $this->getHookBody($request);

        // Actions::execute(PdkBackendActions::UPDATE_SHIPMENTS, [
        //     'orderIds'    => [$content['shipment_reference_identifier']],
        //     'shipmentIds' => [$content['shipment_id']],
        // ]);
    }

    /**
     * @return string
     */
    protected function getHookEvent(): string
    {
        return WebhookSubscription::SHIPMENT_LABEL_CREATED;
    }
}
