<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Order;

use MyParcelNL\Pdk\Base\PdkActions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\Response;

class ExportOrderAction extends AbstractOrderAction
{
    /**
     * @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository
     */
    private $shipmentRepository;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository $orderRepository
     * @param  \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository       $shipmentRepository
     */
    public function __construct(AbstractPdkOrderRepository $orderRepository, ShipmentRepository $shipmentRepository)
    {
        parent::__construct($orderRepository);
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @param  array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(array $parameters): Response
    {
        $orders    = $this->orderRepository->getMany($parameters['orderIds']);
        $shipments = $orders->generateShipments();
        $concepts  = $this->shipmentRepository->createConcepts($shipments);

        $orders->updateShipments($concepts);

        $this->orderRepository->updateMany($orders);

        return Pdk::execute(PdkActions::GET_ORDER_DATA, ['orderIds' => $parameters['orderIds']]);
    }
}
