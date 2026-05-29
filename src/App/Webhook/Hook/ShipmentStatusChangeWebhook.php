<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Webhook\Service\ShipmentWebhookService;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Symfony\Component\HttpFoundation\Request;

final class ShipmentStatusChangeWebhook extends AbstractHook
{
    /**
     * @var \MyParcelNL\Pdk\App\Webhook\Service\ShipmentWebhookService
     */
    private $shipmentWebhookService;

    /**
     * @param  \MyParcelNL\Pdk\App\Webhook\Service\ShipmentWebhookService $shipmentWebhookService
     */
    public function __construct(ShipmentWebhookService $shipmentWebhookService)
    {
        $this->shipmentWebhookService = $shipmentWebhookService;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function handle(Request $request): void
    {
        $this->shipmentWebhookService->handleStatusChange($this->getHookBody($request));
    }

    /**
     * @return string
     */
    protected function getHookEvent(): string
    {
        return WebhookSubscription::SHIPMENT_STATUS_CHANGE;
    }
}
