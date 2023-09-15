<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Utils;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractOrderAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface
     */
    protected $pdkOrderRepository;

    public function __construct(PdkOrderRepositoryInterface $pdkOrderRepository)
    {
        $this->pdkOrderRepository = $pdkOrderRepository;
    }

    /**
     * @return string|string[]
     */
    protected function getOrderIds(Request $request)
    {
        return $request->get('orderIds', []);
    }

    protected function getShipmentIds(Request $request, PdkOrderCollection $orders): array
    {
        $shipmentIds = $request->get('shipmentIds', []);

        if ($shipmentIds) {
            return Utils::toArray($shipmentIds);
        }

        if ($orders->isNotEmpty()) {
            return $orders->getAllShipments()
                ->pluck('id')
                ->all();
        }

        throw new InvalidArgumentException('No shipmentIds or orderIds found in request');
    }

    protected function updateOrders(Request $request): PdkOrderCollection
    {
        $orders = $this->pdkOrderRepository->getMany($this->getOrderIds($request));

        $body       = json_decode($request->getContent(), true);
        $attributes = Utils::filterNull($body['data']['orders'][0] ?? []);

        return $orders->map(fn(PdkOrder $pdkOrder) => $pdkOrder->fill($attributes));
    }
}
