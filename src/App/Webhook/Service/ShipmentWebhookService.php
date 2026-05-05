<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Service;

use MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Shipment\Service\ShipmentUpdateService;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

class ShipmentWebhookService
{
    /**
     * @var \MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface
     */
    private $accountFeaturesService;

    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface
     */
    private $pdkOrderRepository;

    /**
     * @var \MyParcelNL\Pdk\App\Shipment\Service\ShipmentUpdateService
     */
    private $shipmentUpdateService;

    /**
     * @param  \MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface $accountFeaturesService
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface   $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\App\Shipment\Service\ShipmentUpdateService       $shipmentUpdateService
     */
    public function __construct(
        AccountFeaturesServiceInterface $accountFeaturesService,
        PdkOrderRepositoryInterface     $pdkOrderRepository,
        ShipmentUpdateService           $shipmentUpdateService
    ) {
        $this->accountFeaturesService = $accountFeaturesService;
        $this->pdkOrderRepository     = $pdkOrderRepository;
        $this->shipmentUpdateService  = $shipmentUpdateService;
    }

    /**
     * @param  array $content
     *
     * @return void
     */
    public function handleStatusChange(array $content): void
    {
        $orderModeVersion = (int) $this->accountFeaturesService->getOrderModeVersion();

        if (AccountFeaturesServiceInterface::ORDER_MODE_V2 === $orderModeVersion) {
            $this->updateOrderV2Shipment($content, true, $this->getStatusSettingFromWebhook($content));

            return;
        }

        $orderIds = AccountFeaturesServiceInterface::ORDER_MODE_V1 === $orderModeVersion
            ? $this->getOrderIdsForOrderV1($content)
            : $this->getOrderIdsFromShipmentReference($content);

        if (empty($orderIds)) {
            $this->logSkippedWebhook(
                'Skipping shipment status change webhook without a valid order identifier',
                $content
            );

            return;
        }

        $this->updateShipmentsFromApi($orderIds, $content);
    }

    /**
     * @param  array $content
     *
     * @return void
     */
    public function handleLabelCreated(array $content): void
    {
        $orderModeVersion = (int) $this->accountFeaturesService->getOrderModeVersion();

        if (AccountFeaturesServiceInterface::ORDER_MODE_V2 !== $orderModeVersion) {
            return;
        }

        $this->updateOrderV2Shipment($content, false, OrderSettings::STATUS_ON_LABEL_CREATE, true);
    }

    /**
     * @param  array $content
     *
     * @return string[]
     */
    private function getOrderIdsForOrderV1(array $content): array
    {
        $apiIdentifier = $this->getTrimmedValue($content, 'order_id');

        if ('' === $apiIdentifier) {
            return [];
        }

        $order = $this->pdkOrderRepository->getByApiIdentifier($apiIdentifier);

        if (! $order || ! $order->externalIdentifier) {
            return [];
        }

        return [$order->externalIdentifier];
    }

    /**
     * @param  array $content
     *
     * @return string[]
     */
    private function getOrderIdsFromShipmentReference(array $content): array
    {
        $orderId = $this->getTrimmedValue($content, 'shipment_reference_identifier');

        return '' === $orderId ? [] : [$orderId];
    }

    /**
     * @param  array  $content
     * @param  bool   $requireExistingShipment
     * @param  string $orderStatus
     * @param  bool   $preserveExistingStatus
     *
     * @return null|\MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    private function updateOrderV2Shipment(
        array $content,
        bool $requireExistingShipment,
        string $orderStatus,
        bool $preserveExistingStatus = false
    ): ?PdkOrder {
        $orderId = $this->getTrimmedValue($content, 'shipment_reference_identifier');

        if ('' === $orderId) {
            $this->logSkippedWebhook(
                'Skipping order v2 shipment webhook without a shipment reference identifier',
                $content
            );

            return null;
        }

        $shipmentId = Shipment::getIdFromWebhookPayload($content);

        if (null === $shipmentId) {
            $this->logSkippedWebhook('Skipping order v2 shipment webhook without a shipment id', $content);

            return null;
        }

        $order = $this->pdkOrderRepository->find($orderId);

        if (! $order) {
            $this->logSkippedWebhook('Skipping order v2 shipment webhook for unknown order', $content);

            return null;
        }

        if (! $order->externalIdentifier) {
            $this->logSkippedWebhook(
                'Skipping order v2 shipment webhook for an order without external identifier',
                $content
            );

            return null;
        }

        $existingShipment = $this->getShipmentFromOrder($order, $shipmentId);

        if ($requireExistingShipment && ! $existingShipment) {
            $this->logSkippedWebhook('Skipping order v2 shipment status webhook for unknown shipment', $content);

            return null;
        }

        $shipment = Shipment::fromWebhookPayload(
            $content,
            $order->externalIdentifier,
            $existingShipment,
            $preserveExistingStatus
        );

        $this->shipmentUpdateService->update(
            new PdkOrderCollection([$order]),
            new ShipmentCollection([$shipment]),
            $orderStatus
        );

        return $order;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     * @param  int                                      $shipmentId
     *
     * @return null|\MyParcelNL\Pdk\Shipment\Model\Shipment
     */
    private function getShipmentFromOrder(PdkOrder $order, int $shipmentId): ?Shipment
    {
        return $order->shipments->first(function (Shipment $shipment) use ($shipmentId) {
            return (int) $shipment->id === $shipmentId;
        });
    }

    /**
     * @param  array $content
     *
     * @return string
     */
    private function getStatusSettingFromWebhook(array $content): string
    {
        $shipmentContent = Shipment::getWebhookShipmentContent($content);

        return OrderSettings::getStatus((int) ($shipmentContent['status'] ?? null));
    }

    /**
     * @param  array  $content
     * @param  string $key
     *
     * @return string
     */
    private function getTrimmedValue(array $content, string $key): string
    {
        return isset($content[$key]) ? trim((string) $content[$key]) : '';
    }

    /**
     * @param  string[] $orderIds
     * @param  array    $content
     *
     * @return void
     */
    private function updateShipmentsFromApi(array $orderIds, array $content): void
    {
        $shipmentId = Shipment::getIdFromWebhookPayload($content);

        if (null === $shipmentId) {
            $this->logSkippedWebhook('Skipping shipment webhook without a shipment id', $content);

            return;
        }

        Actions::execute(PdkBackendActions::UPDATE_SHIPMENTS, [
            'orderIds'                      => $orderIds,
            'shipmentIds'                   => [$shipmentId],
            'orderStatus'                   => $this->getStatusSettingFromWebhook($content),
            'linkFirstShipmentToFirstOrder' => true,
        ]);
    }

    /**
     * @param  string $message
     * @param  array  $content
     *
     * @return void
     */
    private function logSkippedWebhook(string $message, array $content): void
    {
        Logger::debug($message, [
            'shipment_id'                   => $content['shipment_id'] ?? null,
            'order_id'                      => $content['order_id'] ?? null,
            'shipment_reference_identifier' => $content['shipment_reference_identifier'] ?? null,
        ]);
    }
}
