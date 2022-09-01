<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderLineCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
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

        $this->orders = (new OrderCollection(
            array_map(function (array $order) {
                return $this->createOrderFromApiData($order);
            }, $orders)
        ));
    }

    /**
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Fulfilment\Model\Order
     * @throws \Exception
     */
    private function createOrderFromApiData(array $data): Order
    {
        return new Order([
            'accountId'                   => null,
            'createdAt'                   => $data['created_at'],
            'externalIdentifier'          => $data['external_identifier'],
            'fulfilmentPartnerIdentifier' => $data['fulfilment_partner_identifier'],
            'invoiceAddress'              => new ContactDetails($data['invoice_address'] ?? []),
            'language'                    => $data['language'],
            'orderDate'                   => $data['order_date'],
            'orderLines'                  => new OrderLineCollection($data['order_lines'] ?? []),
            'price'                       => $data['price'],
            'shipment'                    => $this->decodeShipment($data['shipment']),
            'shopId'                      => $data['shop_id'],
            'status'                      => $data['status'],
            'type'                        => $data['type'],
            'updatedAt'                   => $data['updated_at'],
            'uuid'                        => $data['uuid'],
            'vat'                         => $data['vat'],
        ]);
    }
}
