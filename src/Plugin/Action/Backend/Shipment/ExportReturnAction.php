<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Backend\Shipment;

use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Plugin\Action\Backend\Order\AbstractOrderAction;
use MyParcelNL\Pdk\Plugin\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Plugin\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportReturnAction extends AbstractOrderAction
{
    /**
     * @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository
     */
    private $shipmentRepository;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Contract\PdkOrderRepositoryInterface $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository      $shipmentRepository
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
     * @throws \Exception
     */
    public function handle(Request $request): Response
    {
        $orders    = $this->pdkOrderRepository->getMany($this->getOrderIds($request));
        $shipments = $orders->getLastShipments();
        $shipments = $this->shipmentRepository->createReturnShipments($shipments);

        $orders->updateShipments($shipments);

        $this->pdkOrderRepository->updateMany($orders);

        $orderIds = $orders->pluck('externalIdentifier')
            ->toArray();

        return Actions::execute(PdkBackendActions::FETCH_ORDERS, ['orderIds' => $orderIds]);
    }
}
