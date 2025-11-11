<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
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
     * @return string[]
     */
    protected function getOrderIds(Request $request): array
    {
        return Arr::wrap($request->get('orderIds', []));
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
                ->all();
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

        return $orders->map(function (PdkOrder $pdkOrder) use ($attributes) {
            /*
             Merge nested attributes with existing data (just like unnested attributes will be).
             Explicit unsets must be done by setting the value to null.
             This is currently implemented for deliveryOptions and physicalProperties, but could (should) be extended to other attributes in the future.
            */
            $nestedAttributes = ['deliveryOptions', 'physicalProperties'];

            foreach ($nestedAttributes as $nestedAttribute) {
                if (array_key_exists($nestedAttribute, $attributes)) {
                    if (null !== $attributes[$nestedAttribute]) {
                        $attributes[$nestedAttribute] = \array_replace_recursive(
                            $pdkOrder->{$nestedAttribute}->toArray(),
                            $attributes[$nestedAttribute]
                        );
                    } else {
                        // If the nested attribute is explicitly set to null, we should unset it in the PdkOrder.
                        $attributes[$nestedAttribute] = null;
                    }
                }
            }

            return $pdkOrder->fill($attributes);
        });
    }
}
