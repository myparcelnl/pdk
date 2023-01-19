<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Backend\Order;

use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Plugin\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Repository\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportOrderAction extends AbstractOrderAction
{
    /**
     * @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository
     */
    private $shipmentRepository;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Repository\PdkOrderRepositoryInterface $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository        $shipmentRepository
     */
    public function __construct(
        PdkOrderRepositoryInterface $pdkOrderRepository,
        ShipmentRepository          $shipmentRepository
    ) {
        parent::__construct($pdkOrderRepository);
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $orders = $this->updateOrders($request);
        $orders = $this->export($orders, $request);

        $this->pdkOrderRepository->updateMany($orders);

        return Actions::execute(PdkBackendActions::FETCH_ORDERS, [
            'orderIds' => $this->getOrderIds($request),
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection $orders
     * @param  \Symfony\Component\HttpFoundation\Request            $request
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection
     */
    protected function export(PdkOrderCollection $orders, Request $request): PdkOrderCollection
    {
        if (Settings::get(GeneralSettings::ORDER_MODE, GeneralSettings::ID)) {
            return $this->exportOrders($orders);
        }

        return $this->exportShipments($orders, $request);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection $orders
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection
     */
    protected function exportOrders(PdkOrderCollection $orders): PdkOrderCollection
    {
        $fulfilmentOrders = new OrderCollection(
            $orders
                ->map(function (PdkOrder $order) {
                    $order->exported = true;
                    return Order::fromPdkOrder($order);
                })
                ->toArray()
        );

        /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository $orderRepository */
        $orderRepository = Pdk::get(OrderRepository::class);
        $orderRepository->postOrders($fulfilmentOrders);

        return $orders;
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection $orders
     * @param  \Symfony\Component\HttpFoundation\Request            $request
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection
     */
    protected function exportShipments(PdkOrderCollection $orders, Request $request): PdkOrderCollection
    {
        $data      = json_decode($request->getContent(), true);
        $shipments = $orders->generateShipments($data['data']['orders'] ?? []);
        $concepts  = $this->shipmentRepository->createConcepts($shipments);

        $orders->updateShipments($concepts);

        $orders->each(function (PdkOrder $order) {
            $order->shipments = [$order->shipments->last()];
        });

        return $orders;
    }
}

