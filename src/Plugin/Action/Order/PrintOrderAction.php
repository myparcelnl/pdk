<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Order;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\Response;

class PrintOrderAction extends AbstractOrderAction
{
    /**
     * @param  array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(array $parameters): Response
    {
        $orders    = $this->orderRepository->getMany($parameters['orderIds'] ?? []);
        $shipments = $orders->getAllShipments();

        $method = isset($parameters['downloadPdf']) && $parameters['downloadPdf'] ? 'fetchLabelPdf' : 'fetchLabelLink';

        $shipments = Pdk::get(ShipmentRepository::class)
            ->$method(
                $shipments,
                $parameters['downloadDisplay'] ?? 'a6',
                (array) ($parameters['positions'] ?? null)
            );

        return new JsonResponse($shipments->label->toArray());
    }
}
