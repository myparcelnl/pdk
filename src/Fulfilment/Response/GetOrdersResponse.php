<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Concern\DecodesAddressFields;
use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;

class GetOrdersResponse extends ApiResponseWithBody
{
    use DecodesAddressFields;

    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    protected $orders;

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
        $this->orders = new OrderCollection(
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
                    'invoiceAddress'              => $this->decodeAddress($order['invoice_address']),
                    'lines'                       => $order['order_lines'] ?? [],
                    'shipment'                    => $this->decodeShipment($order['shipment'] ?? []),
                    'createdAt'                   => $order['created_at'],
                    'updatedAt'                   => $order['updated_at'],
                ];
            }, $orders)
        );
    }

    /**
     * @param  array $shipment
     *
     * @return array
     */
    private function decodeShipment(array $shipment): array
    {
        $data = Utils::changeArrayKeysCase($this->filter($shipment) ?? [], Arrayable::RECURSIVE);

        $additionalData = [
            'options'   => $data['options'] ?? [],
            'recipient' => $this->decodeAddress($data['recipient']),
            'sender'    => $this->decodeAddress($data['sender']),
        ];

        return array_replace($data, $additionalData);
    }
}
