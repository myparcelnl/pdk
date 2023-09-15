<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportOrderAction extends AbstractOrderAction
{
    public function __construct(
        PdkOrderRepositoryInterface         $pdkOrderRepository,
        private readonly OrderRepository    $orderRepository,
        private readonly ShipmentRepository $shipmentRepository
    ) {
        parent::__construct($pdkOrderRepository);
    }

    /**
     * @throws \Exception
     */
    public function handle(Request $request): Response
    {
        $originalOrders = $this->updateOrders($request);
        $validOrders    = $this->validateOrders($originalOrders);
        $exportedOrders = $this->export($validOrders, $request);

        $this->saveOrders($exportedOrders, $originalOrders);

        return Actions::execute(PdkBackendActions::FETCH_ORDERS, [
            'orderIds' => $this->getOrderIds($request),
        ]);
    }

    /**
     * @throws \Exception
     */
    protected function export(PdkOrderCollection $orders, Request $request): PdkOrderCollection
    {
        if (! Settings::get(OrderSettings::ORDER_MODE, OrderSettings::ID)) {
            return $this->exportShipments($orders);
        }

        $response = $this->exportOrders($orders);

        Actions::execute(PdkBackendActions::POST_ORDER_NOTES, [
            'orderIds' => $this->getOrderIds($request),
        ]);

        return $response;
    }

    protected function exportOrders(PdkOrderCollection $orders): PdkOrderCollection
    {
        $fulfilmentOrders = new OrderCollection(
            $orders
                ->map(function (PdkOrder $order) {
                    $order->exported = true;

                    if ($this->notSharingCustomerInformation()) {
                        $order->shippingAddress->phone = null;
                        $order->shippingAddress->email = null;

                        if ($order->billingAddress) {
                            $order->billingAddress->phone = null;
                            $order->billingAddress->email = null;
                        }
                    }

                    return Order::fromPdkOrder($order);
                })
                ->all()
        );

        $apiOrders = $this->orderRepository->postOrders($fulfilmentOrders);

        $orders->addApiIdentifiers($apiOrders);

        return $orders;
    }

    /**
     * @throws \Exception
     */
    protected function exportShipments(PdkOrderCollection $orders): PdkOrderCollection
    {
        $shipments = $orders->generateShipments();

        if ($shipments->isEmpty()) {
            return $orders;
        }

        if ($this->notSharingCustomerInformation()) {
            $shipments->each(function (Shipment $shipment) {
                $shipment->recipient->email = null;
                $shipment->recipient->phone = null;
            });
        }

        $concepts = $this->shipmentRepository->createConcepts($shipments);

        $orders->updateShipments($concepts);

        $orders->each(function (PdkOrder $order) {
            $order->shipments = [$order->shipments->last()];
        });

        if (! Settings::get(OrderSettings::CONCEPT_SHIPMENTS, OrderSettings::ID)) {
            $this->shipmentRepository->fetchLabelLink($concepts, LabelSettings::FORMAT_A4);

            $shipmentsWithBarcode = $this->shipmentRepository->getShipments(
                $concepts->pluck('id')
                    ->toArray()
            );

            $orders->updateShipments($shipmentsWithBarcode);
        }

        return $orders;
    }

    protected function notSharingCustomerInformation(): bool
    {
        return ! Settings::get(OrderSettings::SHARE_CUSTOMER_INFORMATION, OrderSettings::ID);
    }

    protected function validateOrders(PdkOrderCollection $orders): PdkOrderCollection
    {
        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $orderService */
        $orderService = Pdk::get(PdkOrderOptionsServiceInterface::class);

        return $orders
            ->map(static fn(PdkOrder $order) => $orderService->calculate($order))
            ->filter(static function (PdkOrder $order) {
                $validator = $order->getValidator();

                if ($validator->validate()) {
                    return true;
                }

                $validatorErrors = $validator->getErrors();

                Logger::error('Failed to export order', [
                    'order'       => $order->externalIdentifier,
                    'description' => $validator->getDescription(),
                    'errors'      => $validatorErrors,
                ]);

                Notifications::error(
                    "Failed to export order $order->externalIdentifier",
                    array_map(static fn(array $error) => sprintf('%s: %s', $error['property'], $error['message']),
                        $validatorErrors)
                );

                return false;
            });
    }

    /**
     * Reset shipment options that were originally set to "inherit" because they've been modified by the PdkOrderOptionsService.
     */
    private function saveOrders(PdkOrderCollection $orders, PdkOrderCollection $originalOrders): void
    {
        $orders->each(function (PdkOrder $order) use ($originalOrders) {
            $originalOrder = $originalOrders->firstWhere('externalIdentifier', $order->externalIdentifier);

            if (! $originalOrder) {
                return;
            }

            $inheritedAttributes = Arr::where(
                $originalOrder->deliveryOptions->shipmentOptions->getAttributes(),
                static fn($value) => TriStateService::INHERIT === $value
            );

            $order->deliveryOptions->shipmentOptions->fill($inheritedAttributes);
        });

        // TODO: remove this as soon as saving to repository is fixed
        $orders->each(function (PdkOrder $order) {
            $this->pdkOrderRepository->save($order->externalIdentifier, $order);
        });

        $this->pdkOrderRepository->updateMany($orders);
    }
}

