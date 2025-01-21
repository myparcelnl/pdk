<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Audit\Model\Audit;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Types\Service\TriStateService;
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
        $validOrders    = $this->validateOrders($originalOrders, $request);
        $exportedOrders = $this->export($validOrders, $request);
        $isAutomatic    = Audit::TYPE_AUTOMATIC === $request->get('actionType');

        $exportedOrders->each(function (PdkOrder $order) use ($isAutomatic) {
            if (true === $order->autoExported) {
                return;
            }
            $order->autoExported = $isAutomatic;
        });

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

        $concepts = $this->shipmentRepository->createConcepts($shipments);

        if (Settings::get(OrderSettings::CONCEPT_SHIPMENTS, OrderSettings::ID)) {
            $orders->updateShipments($concepts);
        } else {
            $this->shipmentRepository->fetchLabelLink($concepts, LabelSettings::FORMAT_A4);

            $ids                  = $concepts->pluck('id');
            $shipmentsWithBarcode = $this->shipmentRepository->getShipments($ids->toArray());

            $orders->updateShipments($shipmentsWithBarcode);
        }

        return $orders;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $orders
     * @param  \Symfony\Component\HttpFoundation\Request               $request
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    protected function validateOrders(PdkOrderCollection $orders, Request $request): PdkOrderCollection
    {
        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $orderService */
        $orderService = Pdk::get(PdkOrderOptionsServiceInterface::class);
        $isAutomatic  = Audit::TYPE_AUTOMATIC === $request->get('actionType');

        return $orders
            ->filter(function (PdkOrder $order) use ($isAutomatic) {
                if (! $isAutomatic) {
                    return true;
                }

                if (isset($order->autoExported)) {
                    return ! $order->autoExported;
                }

                return ! $order->audits
                    ->automatic()
                    ->hasAction(PdkBackendActions::EXPORT_ORDERS);
            })
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

