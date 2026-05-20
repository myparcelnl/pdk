<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Service;

use MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;

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
        $effectiveMode = (int) $this->accountFeaturesService->getEffectiveOrderMode();

        $orderIds = $this->getOrderIds($content, $effectiveMode);

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
        $effectiveMode = (int) $this->accountFeaturesService->getEffectiveOrderMode();

        // V1 has its own order-id flow via handleStatusChange and does not process
        // label_created webhooks. V2 and SHIPMENTS both arrive on the wire with
        // shipment_reference_identifier === plugin externalIdentifier (whether the
        // shipment was created via V2 fulfillment or a manual export), so one path
        // covers both.
        if (AccountFeaturesServiceInterface::ORDER_MODE_V1 === $effectiveMode) {
            return;
        }

        $orderIds = $this->getOrderIdsFromShipmentReference($content);

        if (empty($orderIds)) {
            $this->logSkippedWebhook(
                'Skipping shipment label created webhook without a shipment reference identifier',
                $content
            );

            return;
        }

        $this->updateShipmentsFromApi($orderIds, $content);
    }

    /**
     * @param  array $content
     * @param  int   $effectiveMode
     *
     * @return string[]
     */
    private function getOrderIds(array $content, int $effectiveMode): array
    {
        if (AccountFeaturesServiceInterface::ORDER_MODE_V1 === $effectiveMode) {
            return $this->getOrderIdsForOrderV1($content);
        }

        return $this->getOrderIdsFromShipmentReference($content);
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
        $shipmentId = $this->getShipmentId($content);

        if (null === $shipmentId) {
            $this->logSkippedWebhook('Skipping shipment webhook without a shipment id', $content);

            return;
        }

        Actions::execute(PdkBackendActions::UPDATE_SHIPMENTS, [
            'orderIds'                        => $orderIds,
            'shipmentIds'                     => [$shipmentId],
            'useShipmentStatusForOrderStatus' => true,
            'linkFirstShipmentToFirstOrder'   => true,
        ]);
    }

    /**
     * @param  array $content
     *
     * @return null|int
     */
    private function getShipmentId(array $content): ?int
    {
        $content = $this->getShipmentContent($content);

        foreach (['shipment_id', 'shipmentId', 'id'] as $key) {
            $value = $this->getTrimmedValue($content, $key);

            if ('' !== $value && is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
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
