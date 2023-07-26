<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractOrderAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface
     */
    protected $pdkOrderRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $pdkOrderRepository
     */
    public function __construct(PdkOrderRepositoryInterface $pdkOrderRepository)
    {
        $this->pdkOrderRepository = $pdkOrderRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string|string[]
     */
    protected function getOrderIds(Request $request)
    {
        return $request->get('orderIds', []);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request               $request
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $orders
     *
     * @return array
     */
    protected function getShipmentIds(Request $request, PdkOrderCollection $orders): array
    {
        $shipmentIds = $request->get('shipmentIds', []);

        if ($shipmentIds) {
            return Utils::toArray($shipmentIds);
        }

        if ($orders->isNotEmpty()) {
            return $orders->getAllShipments()
                ->pluck('id')
                ->toArray();
        }

        throw new InvalidArgumentException('No shipmentIds or orderIds found in request');
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    protected function updateOrders(Request $request): PdkOrderCollection
    {
        $orders = $this->pdkOrderRepository->getMany($this->getOrderIds($request));

        $body       = json_decode($request->getContent(), true);
        $attributes = Utils::filterNull($body['data']['orders'][0] ?? []);

        /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface $shipmentOptionsService */
        $shipmentOptionsService = Pdk::get(ShipmentOptionsServiceInterface::class);

        return $orders->map(function (PdkOrder $pdkOrder) use ($shipmentOptionsService, $attributes) {
            return $shipmentOptionsService->calculate($pdkOrder->fill($attributes));
        });
    }
}
