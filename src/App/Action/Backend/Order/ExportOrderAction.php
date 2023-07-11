<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
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
        $orders    = $this->updateOrders($request);
        $newOrders = $this->export($orders, $request);

        $this->pdkOrderRepository->updateMany($newOrders);

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
        if (Settings::get(GeneralSettings::ORDER_MODE, GeneralSettings::ID)) {
            return $this->exportOrders($orders);
        }

        return $this->exportShipments($orders, $request);
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
                ->toArray()
        );

        $apiOrders = $this->orderRepository->postOrders($fulfilmentOrders);

        return $orders->addApiIdentifiers($apiOrders);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $orders
     * @param  \Symfony\Component\HttpFoundation\Request               $request
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     * @throws \Exception
     */
    protected function exportShipments(PdkOrderCollection $orders, Request $request): PdkOrderCollection
    {
        $data      = json_decode($request->getContent(), true);
        $shipments = $orders->generateShipments($data['data']['orders'][0] ?? []);

        if ($shipments->isEmpty()) {
            return $orders;
        }

        $concepts = $this->shipmentRepository->createConcepts($shipments);

        $orders->updateShipments($concepts);

        $orders->each(function (PdkOrder $order) {
            $order->shipments = [$order->shipments->last()];
        });

        return $orders;
    }
}

