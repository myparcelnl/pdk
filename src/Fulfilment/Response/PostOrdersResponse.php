<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;

class PostOrdersResponse extends ApiResponseWithBody
{
    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    private $orders;

    /**
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    public function getOrderCollection(): OrderCollection
    {
        return $this->orders;
    }

    protected function parseResponseBody(): void
    {
        $parsedBody   = json_decode($this->getBody(), true);
        $orders       = $parsedBody['data']['orders'] ?? [];
        $this->orders = new OrderCollection(array_map([$this, 'decodeOrder'], $orders));
    }

    protected function decodeOrder(array $data): Order
    {
        // Convert carrier ID to name
        $shipmentData = $data['shipment'] ?? [];
        $carrierName  = Utils::convertToName($shipmentData['carrier'] ?? null, Carrier::CARRIER_NAME_ID_MAP);

        $shipment = \array_merge($shipmentData, ['carrier' => $carrierName]);

        return new Order([
            'uuid'                        => $data['uuid'] ?? null,
            'shopId'                      => $data['shop_id'] ?? null,
            'accountId'                   => $data['account_id'] ?? null,
            'externalIdentifier'          => $data['external_identifier'] ?? null,
            'fulfilmentPartnerIdentifier' => $data['fulfilment_partner_identifier'] ?? null,
            'language'                    => $data['language'] ?? null,
            'orderDate'                   => $data['order_date'] ?? null,
            'status'                      => $data['status'] ?? null,
            'type'                        => $data['type'] ?? null,
            'price'                       => $data['price'] ?? null,
            'vat'                         => $data['vat'] ?? null,
            'priceAfterVat'               => $data['price_after_vat'] ?? null,
            'invoiceAddress'              => $data['invoice_address'] ?? null,
            'lines'                       => $data['order_lines'] ?? [],
            'shipment'                    => $shipment,
            'createdAt'                   => $data['created_at'] ?? null,
            'updatedAt'                   => $data['updated_at'] ?? null,
        ]);
    }
}
