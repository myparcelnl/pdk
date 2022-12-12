<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Order;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
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
        $shipments = $orders->getLastShipments();
        $method    = isset($parameters['download']) && $parameters['download'] ? 'fetchLabelPdf' : 'fetchLabelLink';

        $shipments = Pdk::get(ShipmentRepository::class)
            ->$method(
                $shipments,
                $parameters['display'] ?? LabelSettings::FORMAT_A6,
                (array) ($parameters['positions'] ?? null)
            );

        return new JsonResponse($shipments->label->toArray());
    }
}
