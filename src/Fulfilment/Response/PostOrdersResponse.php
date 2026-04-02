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
        $carrierName = Utils::convertToName($data['carrier_id'] ?? null, Carrier::CARRIER_NAME_ID_MAP);

        $shipment = \array_merge($data['shipment'], ['carrier' => $carrierName]);

        return new Order([
            'uuid'                        => $data['uuid'],
            'shopId'                      => $data['shop_id'],
            'accountId'                   => $data['account_id'],
            'externalIdentifier'          => $data['external_identifier'],
            'fulfilmentPartnerIdentifier' => $data['fulfilment_partner_identifier'],
            'language'                    => $data['language'],
            'orderDate'                   => $data['order_date'],
            'status'                      => $data['status'],
            'type'                        => $data['type'],
            'price'                       => $data['price'],
            'vat'                         => $data['vat'],
            'priceAfterVat'               => $data['price_after_vat'],
            'invoiceAddress'              => $data['invoice_address'],
            'lines'                       => $data['order_lines'] ?? [],
            'shipment'                    => $shipment,
            'createdAt'                   => $data['created_at'],
            'updatedAt'                   => $data['updated_at'],
        ]);
    }
}
