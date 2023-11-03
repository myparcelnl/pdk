<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Shipment\Concern\DecodesRequestShipment;

class GetOrdersResponse extends ApiResponseWithBody
{
    use DecodesRequestShipment;

    /**
     * @var \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    protected $orders;

    /**
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    public function getOrders(): PdkOrderCollection
    {
        return $this->orders;
    }

    /**
     * @param  array $order
     *
     * @return array
     */
    protected function decodeOrder(array $order): array
    {
        return [
            'apiIdentifier'               => Arr::get($order, 'uuid'),
            'shopId'                      => Arr::get($order, 'shop_id'),
            'accountId'                   => Arr::get($order, 'account_id'),
            'externalIdentifier'          => Arr::get($order, 'external_identifier'),
            'fulfilmentPartnerIdentifier' => Arr::get($order, 'fulfilment_partner_identifier'),

            'shippingAddress' => $this->decodeAddress(Arr::get($order, 'shipment.recipient') ?? []),
            'billingAddress'  => $this->decodeAddress($order['invoice_address'] ?? []),
            'deliveryOptions' => $this->decodeDeliveryOptions($order['shipment'] ?? []),
            'lines'           => $this->decodeOrderLines($order['order_lines'] ?? []),
            'shipments'       => $this->decodeOrderShipments($order['order_shipments'] ?? []),

            'status' => Arr::get($order, 'status'),

            'price'         => Arr::get($order, 'price'),
            'vat'           => Arr::get($order, 'vat'),
            'priceAfterVat' => Arr::get($order, 'price_after_vat'),
            'orderDate'     => Arr::get($order, 'order_date'),
            'createdAt'     => Arr::get($order, 'created_at'),
            'updatedAt'     => Arr::get($order, 'updated_at'),
        ];
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function parseResponseBody(): void
    {
        $parsedBody = json_decode($this->getBody(), true);
        $orders     = $parsedBody['data']['orders'] ?? [];

        $this->orders = new PdkOrderCollection(array_map([$this, 'decodeOrder'], $orders));
    }

    /**
     * @param  array $orderLines
     *
     * @return array
     */
    private function decodeOrderLines(array $orderLines): array
    {
        return array_map(static function (array $orderLine) {
            return [
                'apiIdentifier'   => Arr::get($orderLine, 'uuid'),
                'quantity'        => Arr::get($orderLine, 'quantity'),
                'instructions'    => Arr::get($orderLine, 'instructions'),
                'price'           => Arr::get($orderLine, 'price'),
                'price_after_vat' => Arr::get($orderLine, 'price_after_vat'),
                'vat'             => Arr::get($orderLine, 'vat'),
                'product'         => [
                    'externalIdentifier' => Arr::get($orderLine, 'product.external_identifier'),
                    'description'        => Arr::get($orderLine, 'product.description'),
                    'height'             => Arr::get($orderLine, 'product.height'),
                    'length'             => Arr::get($orderLine, 'product.length'),
                    'name'               => Arr::get($orderLine, 'product.name'),
                    'weight'             => Arr::get($orderLine, 'product.weight'),
                    'width'              => Arr::get($orderLine, 'product.width'),
                ],
            ];
        }, $orderLines);
    }

    /**
     * @param  array $orderShipments
     *
     * @return array
     */
    private function decodeOrderShipments(array $orderShipments): array
    {
        $uuids     = Arr::pluck($orderShipments, 'uuid');
        $shipments = Arr::pluck($orderShipments, 'shipment') ?? [];

        $shipmentsWithUuid = array_map(static function (array $shipment, string $uuid) {
            $shipment['uuid'] = $uuid;

            return $shipment;
        }, $shipments, $uuids);

        return array_map([$this, 'decodeShipment'], $shipmentsWithUuid);
    }
}
