<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Order;

use MyParcelNL\Pdk\Base\PdkActions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\Response;

class ExportOrderAction extends AbstractOrderAction
{
    /**
     * @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository
     */
    private $shipmentRepository;

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
        $orders = $this->orderRepository->getMany((array) $parameters['orderIds']);

        $shipments = $orders->generateShipments();
        $shipments = $this->shipmentRepository->createConcepts($shipments);

        if (true === $parameters['print']) {
            $shipments = $this->shipmentRepository->fetchLabelLink($shipments, LabelSettings::FORMAT_A6);
            Pdk::execute(PdkActions::UPDATE_TRACKING_NUMBER);
        }

        $orders->updateShipments($shipments);

        $this->orderRepository->updateMany($orders);

        $orderIds = $orders->pluck('externalIdentifier')
            ->toArray();

        return Pdk::execute(PdkActions::GET_ORDER_DATA, ['orderIds' => $orderIds]);
    }
}
