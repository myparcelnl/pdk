<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Shipment\Concern\HasDecodesShipment;

class GetOrdersResponse extends ApiResponseWithBody
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
                    'uuid'                        => $order['uuid'],
                    'shopId'                      => $order['shop_id'],
                    'accountId'                   => $order['account_id'],
                    'externalIdentifier'          => $order['external_identifier'],
                    'fulfilmentPartnerIdentifier' => $order['fulfilment_partner_identifier'],
                    'language'                    => $order['language'],
                    'orderDate'                   => $order['order_date'],
                    'status'                      => $order['status'],
                    'type'                        => $order['type'],
                    'price'                       => $order['price'],
                    'vat'                         => $order['vat'],
                    'priceAfterVat'               => $order['price_after_vat'],
                    'invoiceAddress'              => $order['invoice_address'],
                    'orderLines'                  => $order['order_lines'] ?? [],
                    'shipment'                    => $this->decodeShipment($order['shipment'] ?? []),
                    'createdAt'                   => $order['created_at'],
                    'updatedAt'                   => $order['updated_at'],
                ];
            }, $orders)
        ));
    }
}
