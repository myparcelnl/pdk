<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Shipment\Concern\HasDecodesShipment;

class GetOrdersResponse extends AbstractApiResponseWithBody
{
    use HasDecodesShipment;

    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    private $orders;

    /**
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    public function getOrders(): OrderCollection
    {
        return $this->orders;
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function parseResponseBody(): void
    {
        $parsedBody = json_decode($this->getBody(), true);
        $orders     = $parsedBody['data']['orders'] ?? [];

        $this->createOrders($orders);
    }

    /**
     * @param  array $orders
     *
     * @return void
     * @throws \Exception
     */
    private function createOrders(array $orders): void
    {
        $this->orders = (new OrderCollection(
            array_map(function (array $order) {
                return [
                    'accountId'                   => null,
                    'createdAt'                   => $order['created_at'],
                    'externalIdentifier'          => $order['external_identifier'],
                    'fulfilmentPartnerIdentifier' => $order['fulfilment_partner_identifier'],
                    'invoiceAddress'              => $order['invoice_address'],
                    'language'                    => $order['language'],
                    'orderDate'                   => $order['order_date'],
                    'orderLines'                  => $order['order_lines'] ?? [],
                    'price'                       => $order['price'],
                    'shipment'                    => $this->decodeShipment($order['shipment']),
                    'shopId'                      => $order['shop_id'],
                    'status'                      => $order['status'],
                    'type'                        => $order['type'],
                    'updatedAt'                   => $order['updated_at'],
                    'uuid'                        => $order['uuid'],
                    'vat'                         => $order['vat'],
                ];
            }, $orders)
        ));
    }
}
