<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Shipment;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Backend\Order\AbstractOrderAction;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Shipment\Service\ShipmentUpdateService;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateShipmentsAction extends AbstractOrderAction
{
    /**
     * @var \MyParcelNL\Pdk\App\Shipment\Service\ShipmentUpdateService
     */
    private $shipmentUpdateService;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository
     */
    private $shipmentRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository         $shipmentRepository
     * @param  \MyParcelNL\Pdk\App\Shipment\Service\ShipmentUpdateService     $shipmentUpdateService
     */
    public function __construct(
        PdkOrderRepositoryInterface     $pdkOrderRepository,
        ShipmentRepository              $shipmentRepository,
        ShipmentUpdateService           $shipmentUpdateService
    ) {
        parent::__construct($pdkOrderRepository);
        $this->shipmentRepository    = $shipmentRepository;
        $this->shipmentUpdateService = $shipmentUpdateService;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $orders    = $this->pdkOrderRepository->getMany($this->getOrderIds($request));
        $shipments = $this->shipmentRepository->getShipments($this->getShipmentIds($request, $orders));

        $this->shipmentUpdateService->update(
            $orders,
            $shipments,
            $request->get('orderStatus', OrderSettings::STATUS_ON_LABEL_CREATE),
            (bool) $request->get('linkFirstShipmentToFirstOrder')
        );

        return new JsonResponse([
            'shipments' => $shipments->toStorableArray(),
        ]);
    }
}
