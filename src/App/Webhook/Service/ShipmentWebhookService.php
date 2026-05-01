<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Service;

use MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

final class ShipmentWebhookService
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
     * @param  \MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface $accountFeaturesService
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface   $pdkOrderRepository
     */
    public function __construct(
        AccountFeaturesServiceInterface $accountFeaturesService,
        PdkOrderRepositoryInterface     $pdkOrderRepository
    ) {
        $this->accountFeaturesService = $accountFeaturesService;
        $this->pdkOrderRepository     = $pdkOrderRepository;
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
            $order = $this->mergeOrderV2Shipment($content, true);

            if ($order) {
                $this->updateOrderStatus([$order->externalIdentifier], $content);
            }

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
        if (AccountFeaturesServiceInterface::ORDER_MODE_V2 !== $this->accountFeaturesService->getOrderModeVersion()) {
            return;
        }

        $this->mergeOrderV2Shipment($content, false);
    }

    /**
     * @param  array $content
     *
     * @return string[]
     */
    private function getOrderIdsForOrderV1(array $content): array
    {
        $apiIdentifier = $this->getTrimmedValue($content, 'order_id');

        if ('' === $apiIdentifier || ! method_exists($this->pdkOrderRepository, 'getByApiIdentifier')) {
            return [];
        }

        /** @var null|\MyParcelNL\Pdk\App\Order\Model\PdkOrder $order */
        $order = call_user_func([$this->pdkOrderRepository, 'getByApiIdentifier'], $apiIdentifier);

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
     * @param  array $content
     * @param  bool  $requireExistingShipment
     *
     * @return null|\MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    private function mergeOrderV2Shipment(array $content, bool $requireExistingShipment): ?PdkOrder
    {
        $shipmentContent = $this->getShipmentContent($content);
        $orderId         = $this->getTrimmedValue($content, 'shipment_reference_identifier');

        if ('' === $orderId) {
            $this->logSkippedWebhook(
                'Skipping order v2 shipment webhook without a shipment reference identifier',
                $content
            );

            return null;
        }

        $shipmentId = $this->getShipmentId($shipmentContent);

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

        $shipmentData = $this->getShipmentDataFromWebhook($shipmentContent, $order->externalIdentifier);

        if ($existingShipment) {
            $shipmentData = array_replace($existingShipment->toStorableArray(), $shipmentData);
        }

        $shipments = $order->shipments ?? new ShipmentCollection();

        $order->shipments = new ShipmentCollection(
            $shipments
                ->mergeByKey(new ShipmentCollection([$shipmentData]), 'id')
                ->all()
        );

        $this->pdkOrderRepository->update($order);

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
     * @return array
     */
    private function getShipmentContent(array $content): array
    {
        return isset($content['shipment']) && is_array($content['shipment'])
            ? array_replace($content, $content['shipment'])
            : $content;
    }

    /**
     * @param  array  $content
     * @param  string $orderId
     *
     * @return array
     */
    private function getShipmentDataFromWebhook(array $content, string $orderId): array
    {
        return $this->filterEmptyValues(array_replace($content, [
            'id'                       => $this->getShipmentId($content),
            'orderId'                  => $orderId,
            'referenceIdentifier'      => $orderId,
            'externalIdentifier'       => $this->getFirstTrimmedValue($content, [
                'external_identifier',
                'external_shipment_identifier',
                'externalIdentifier',
            ]),
            'barcode'                  => $this->getFirstTrimmedValue($content, ['barcode']),
            'linkConsumerPortal'       => $this->getFirstTrimmedValue($content, [
                'link_consumer_portal',
                'linkConsumerPortal',
                'track_trace_url',
                'trackTraceUrl',
            ]),
            'multiColloMainShipmentId' => $this->getFirstTrimmedValue($content, [
                'multi_collo_main_shipment_id',
                'multiColloMainShipmentId',
            ]),
            'status'                   => $this->getNullableInt($content, 'status'),
            'shipmentType'             => $this->getNullableInt($content, 'shipment_type'),
            'isReturn'                 => $this->getNullableBool($content, 'is_return'),
            'multiCollo'               => $this->getNullableBool($content, 'multi_collo'),
        ]));
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
     * @param  array    $content
     * @param  string[] $keys
     *
     * @return null|string
     */
    private function getFirstTrimmedValue(array $content, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->getTrimmedValue($content, $key);

            if ('' !== $value) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array $content
     *
     * @return null|int
     */
    private function getShipmentId(array $content): ?int
    {
        foreach (['shipment_id', 'shipmentId', 'id'] as $key) {
            $value = $this->getTrimmedValue($content, $key);

            if ('' !== $value) {
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * @param  array  $content
     * @param  string $key
     *
     * @return null|int
     */
    private function getNullableInt(array $content, string $key): ?int
    {
        $value = $this->getTrimmedValue($content, $key);

        return '' === $value ? null : (int) $value;
    }

    /**
     * @param  array  $content
     * @param  string $key
     *
     * @return null|bool
     */
    private function getNullableBool(array $content, string $key): ?bool
    {
        if (! array_key_exists($key, $content)) {
            return null;
        }

        return (bool) $content[$key];
    }

    /**
     * @param  array $data
     *
     * @return array
     */
    private function filterEmptyValues(array $data): array
    {
        return array_filter($data, static function ($value): bool {
            return null !== $value && '' !== $value;
        });
    }

    /**
     * @param  string[] $orderIds
     * @param  array    $content
     *
     * @return void
     */
    private function updateShipmentsFromApi(array $orderIds, array $content): void
    {
        Actions::execute(PdkBackendActions::UPDATE_SHIPMENTS, [
            'orderIds'                      => $orderIds,
            'shipmentIds'                   => [$content['shipment_id']],
            'orderStatus'                   => OrderSettings::getStatus((int) ($content['status'] ?? null)),
            'linkFirstShipmentToFirstOrder' => true,
        ]);
    }

    /**
     * @param  string[] $orderIds
     * @param  array    $content
     *
     * @return void
     */
    private function updateOrderStatus(array $orderIds, array $content): void
    {
        Actions::execute(PdkBackendActions::UPDATE_ORDER_STATUS, [
            'orderIds' => $orderIds,
            'setting'  => OrderSettings::getStatus((int) ($content['status'] ?? null)),
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
