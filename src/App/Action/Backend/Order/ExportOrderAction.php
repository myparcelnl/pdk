<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Notification\Model\NotificationTags;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportOrderAction extends AbstractOrderAction
{
    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository
     */
    private $shipmentRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository          $orderRepository
     * @param  \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository         $shipmentRepository
     */
    public function __construct(
        PdkOrderRepositoryInterface $pdkOrderRepository,
        OrderRepository             $orderRepository,
        ShipmentRepository          $shipmentRepository
    ) {
        parent::__construct($pdkOrderRepository);
        $this->orderRepository    = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $orders
     * @param  \Symfony\Component\HttpFoundation\Request               $request
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
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

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $orders
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    protected function exportOrders(PdkOrderCollection $orders): PdkOrderCollection
    {
        $fulfilmentOrders = new OrderCollection(
            $orders
                ->map(function (PdkOrder $order) {
                    $order->exported = true;

                    if (! $this->sharingCustomerInformation($order->deliveryOptions->carrier)) {
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
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $orders
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     * @throws \Exception
     */
    protected function exportShipments(PdkOrderCollection $orders): PdkOrderCollection
    {
        $shipments = $orders->generateShipments();

        if ($shipments->isEmpty()) {
            return $orders;
        }

        $shipments->each(function (Shipment $shipment) {
            if (! $this->sharingCustomerInformation($shipment->deliveryOptions->carrier)) {
                $shipment->recipient->email = null;
                $shipment->recipient->phone = null;
            }
        });

        $concepts = $this->shipmentRepository->createConcepts($shipments);

        $orders->updateShipments($concepts);

        $orders->each(function (PdkOrder $order) {
            $order->shipments = $order->shipments->take(-1);
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

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return bool
     */
    protected function sharingCustomerInformation(Carrier $carrier): bool
    {
        /** @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $schema */
        $schema = Pdk::get(CarrierSchema::class);

        $schema->setCarrier($carrier);

        $carrierNeedsCustomerInfo = $schema->needsCustomerInfo();
        $sharingCustomerInfo      = Settings::get(OrderSettings::SHARE_CUSTOMER_INFORMATION, OrderSettings::ID);

        return $carrierNeedsCustomerInfo || $sharingCustomerInfo;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $orders
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    protected function validateOrders(PdkOrderCollection $orders): PdkOrderCollection
    {
        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $orderService */
        $orderService = Pdk::get(PdkOrderOptionsServiceInterface::class);

        return $orders
            ->map(static function (PdkOrder $order) use ($orderService) {
                return $orderService->calculate($order);
            })
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
                    array_map(static function (array $error) {
                        return sprintf('%s: %s', $error['property'], $error['message']);
                    }, $validatorErrors),
                    Notification::CATEGORY_ACTION,
                    [
                        'action'   => PdkBackendActions::EXPORT_ORDERS,
                        'orderIds' => $order->externalIdentifier,
                    ]
                );

                return false;
            });
    }

    /**
     * Reset shipment options that were originally set to "inherit" because they've been modified by the
     * PdkOrderOptionsService.
     *
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $orders
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $originalOrders
     *
     * @return void
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
                static function ($value) {
                    return TriStateService::INHERIT === $value;
                }
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

