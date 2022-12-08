<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Base\Request\Request;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Sdk\src\Support\Collection;

class PostOrdersRequest extends Request
{
    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection $collection
     */
    public function __construct(OrderCollection $collection)
    {
        parent::__construct();
        $this->collection = $collection;
    }

    /**
     * @return null|string
     */
    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'orders' => (new Collection($this->collection))->map(function ($order) {
                    return $this->encodeOrder($order);
                }),
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return '/fulfilment/orders';
    }

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Model\Order $order
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeOrder(Order $order): array
    {
        return [
            'external_identifier'           => $order->externalIdentifier,
            'fulfilment_partner_identifier' => $order->fulfilmentPartnerIdentifier,
            'order_date'                    => $order->orderDate,
            'invoice_address'               => $order->invoiceAddress,
            'order_lines'                   => $order->orderLines->toArrayWithoutNull(),
            'shipment'                      => $order->shipment ? $order->shipment->toSnakeCaseArray() : null,
        ];
    }
}
