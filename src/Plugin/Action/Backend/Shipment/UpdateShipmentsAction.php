<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Backend\Shipment;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\Plugin\Action\Backend\Order\AbstractOrderAction;
use MyParcelNL\Pdk\Plugin\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateShipmentsAction extends AbstractOrderAction
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
     */
    public function handle(Request $request): Response
    {
        $orderIds = $this->getOrderIds($request);
        $orders   = $this->pdkOrderRepository->getMany($orderIds);

        $shipmentIds = $this->getShipmentIds($request, $orders);
        $shipments   = $this->shipmentRepository->getShipments($shipmentIds);

        if ($orders->isNotEmpty()) {
            $orders->updateShipments($shipments);
            $this->pdkOrderRepository->updateMany($orders);
        }

        return new JsonResponse([
            'shipments' => $shipments->toStorableArray(),
        ]);
    }
}
